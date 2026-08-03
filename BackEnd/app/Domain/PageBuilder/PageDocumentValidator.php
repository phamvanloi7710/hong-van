<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;

final readonly class PageDocumentValidator
{
    public function __construct(
        private BlockRegistry $registry,
        private Container $container,
        private PageBuilderMediaResolver $mediaResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function validate(array $document): array
    {
        $errors = [];
        $encoded = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($encoded) || strlen($encoded) > PageDocumentSchema::MAX_BYTES) {
            $errors['document'][] = $this->message('payload_too_large');
        }

        $this->rejectUnknownKeys($document, ['schemaVersion', 'themeVersionId', 'pageSettings', 'blocks'], 'document', $errors);
        if (($document['schemaVersion'] ?? null) !== PageDocumentSchema::VERSION) {
            $errors['document.schemaVersion'][] = $this->message('schema_version');
        }
        $themeVersionId = $document['themeVersionId'] ?? null;
        if ($themeVersionId !== null && (! is_string($themeVersionId) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $themeVersionId) !== 1)) {
            $errors['document.themeVersionId'][] = $this->message('ulid');
        }
        $this->validatePageSettings($document['pageSettings'] ?? null, $errors);

        $blocks = $document['blocks'] ?? null;
        if (! is_array($blocks) || ! array_is_list($blocks)) {
            $errors['document.blocks'][] = $this->message('blocks_list');
            $blocks = [];
        }

        $ids = [];
        $dependencies = [];
        $dependencyPaths = [];
        $count = 0;
        $sanitized = [];
        foreach ($blocks as $index => $block) {
            $sanitized[] = $this->validateBlock($block, "document.blocks.{$index}", null, 1, $count, $ids, $dependencies, $dependencyPaths, $errors);
        }
        $this->validateDependencyGraph($ids, $dependencies, $dependencyPaths, $errors);
        $this->validateHeadingHierarchy($sanitized, $errors);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $document['blocks'] = $sanitized;
        $this->mediaResolver->resolve($document);

        return $document;
    }

    /** @param array<string, mixed> $document */
    public function checksum(array $document): string
    {
        $sorted = $this->sortRecursively($document);

        return hash('sha256', json_encode($sorted, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param  array<string, true>  $ids
     * @param  array<string, list<string>>  $dependencies
     * @param  array<string, string>  $dependencyPaths
     * @param  array<string, list<string>>  $errors
     * @return array<string, mixed>
     */
    private function validateBlock(mixed $value, string $path, ?BlockDefinition $parent, int $depth, int &$count, array &$ids, array &$dependencies, array &$dependencyPaths, array &$errors): array
    {
        if (! is_array($value) || ($value !== [] && array_is_list($value))) {
            $errors[$path][] = $this->message('block_object');

            return [];
        }
        $this->rejectUnknownKeys($value, ['id', 'type', 'version', 'props', 'style', 'visibility', 'bindings', 'children'], $path, $errors);
        $count++;
        if ($count > PageDocumentSchema::MAX_BLOCKS) {
            $errors['document.blocks'][] = $this->message('too_many_blocks');
        }
        if ($depth > PageDocumentSchema::MAX_DEPTH) {
            $errors[$path][] = $this->message('too_deep');
        }

        $id = $value['id'] ?? null;
        if (! is_string($id) || preg_match('/^[A-Za-z0-9_-]{8,64}$/', $id) !== 1) {
            $errors["{$path}.id"][] = $this->message('block_id');
            $id = "invalid-{$count}";
        } elseif (isset($ids[$id])) {
            $errors["{$path}.id"][] = $this->message('duplicate_id');
        } else {
            $ids[$id] = true;
        }

        $type = $value['type'] ?? null;
        if (! is_string($type)) {
            $errors["{$path}.type"][] = $this->message('unknown_block');

            return $value;
        }
        $definition = $this->registry->find($type);
        if (! $definition instanceof BlockDefinition) {
            $errors["{$path}.type"][] = $this->message('unknown_block');

            return $value;
        }
        if (($value['version'] ?? null) !== $definition->version) {
            $errors["{$path}.version"][] = $this->message('block_version');
        }
        if ($parent === null && ! $definition->allowRoot) {
            $errors["{$path}.type"][] = $this->message('invalid_parent');
        }
        if ($parent !== null && (! in_array($parent->type, $definition->allowedParents, true) || ! in_array($definition->type, $parent->allowedChildren, true))) {
            $errors["{$path}.type"][] = $this->message('invalid_child');
        }
        if ($depth > $definition->maxDepth) {
            $errors[$path][] = $this->message('block_depth');
        }

        foreach (['props', 'style', 'visibility', 'bindings'] as $field) {
            $this->validateSchema($value[$field] ?? null, $definition->{$field.'Schema'}, "{$path}.{$field}", $errors);
            $this->rejectExecutablePayload($value[$field] ?? null, "{$path}.{$field}", $errors);
        }
        $this->collectDependencies($value['bindings'] ?? [], $id, "{$path}.bindings", $dependencies, $dependencyPaths);

        $children = $value['children'] ?? null;
        if (! is_array($children) || ! array_is_list($children)) {
            $errors["{$path}.children"][] = $this->message('children_list');
            $children = [];
        }
        if ($children !== [] && $definition->allowedChildren === []) {
            $errors["{$path}.children"][] = $this->message('children_not_allowed');
        }
        if (count($children) < $definition->minChildren) {
            $errors["{$path}.children"][] = $this->message('children_min');
        }
        if (count($children) > $definition->maxChildren) {
            $errors["{$path}.children"][] = $this->message('children_max');
        }
        $sanitizedChildren = [];
        foreach ($children as $index => $child) {
            $sanitizedChildren[] = $this->validateBlock($child, "{$path}.children.{$index}", $definition, $depth + 1, $count, $ids, $dependencies, $dependencyPaths, $errors);
        }
        $value['children'] = $sanitizedChildren;

        $sanitizer = $this->container->make($definition->sanitizer);
        if (! $sanitizer instanceof BlockSanitizer) {
            throw new \LogicException("Invalid sanitizer for Page Builder block [{$definition->type}].");
        }

        return $sanitizer->sanitize($value, $path);
    }

    /** @param array<string, list<string>> $errors */
    private function validatePageSettings(mixed $value, array &$errors): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'container' => ['type' => 'string', 'enum' => ['default', 'wide', 'full']],
                'background' => ['type' => 'string', 'enum' => ['surface', 'muted', 'brand']],
                'hideHeader' => ['type' => 'boolean'],
                'hideFooter' => ['type' => 'boolean'],
            ],
            'required' => ['container', 'background', 'hideHeader', 'hideFooter'],
            'additionalProperties' => false,
        ];
        $this->validateSchema($value, $schema, 'document.pageSettings', $errors);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, list<string>>  $errors
     */
    private function validateSchema(mixed $value, array $schema, string $path, array &$errors): void
    {
        $type = $schema['type'] ?? null;
        if ($type === 'object') {
            if (! is_array($value) || ($value !== [] && array_is_list($value))) {
                $errors[$path][] = $this->message('object');

                return;
            }
            $properties = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
            $required = is_array($schema['required'] ?? null) ? $schema['required'] : [];
            foreach ($required as $key) {
                if (is_string($key) && ! array_key_exists($key, $value)) {
                    $errors["{$path}.{$key}"][] = $this->message('required');
                }
            }
            foreach ($value as $key => $child) {
                if (! is_string($key) || ! isset($properties[$key]) || ! is_array($properties[$key])) {
                    if (($schema['additionalProperties'] ?? true) === false) {
                        $errors["{$path}.{$key}"][] = $this->message('unknown_field');
                    }

                    continue;
                }
                $this->validateSchema($child, $properties[$key], "{$path}.{$key}", $errors);
            }

            return;
        }
        $valid = match ($type) {
            'string' => is_string($value),
            'boolean' => is_bool($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'array' => is_array($value) && array_is_list($value),
            default => false,
        };
        if (! $valid) {
            $errors[$path][] = $this->message('type');

            return;
        }
        if (is_array($schema['enum'] ?? null) && ! in_array($value, $schema['enum'], true)) {
            $errors[$path][] = $this->message('enum');
        }
        if (is_string($value) && isset($schema['maxLength']) && mb_strlen($value) > (int) $schema['maxLength']) {
            $errors[$path][] = $this->message('max_length');
        }
        if (is_string($value) && isset($schema['minLength']) && mb_strlen($value) < (int) $schema['minLength']) {
            $errors[$path][] = $this->message('min_length');
        }
        if (is_string($value) && is_string($schema['pattern'] ?? null) && preg_match('~'.$schema['pattern'].'~D', $value) !== 1) {
            $errors[$path][] = $this->message('pattern');
        }
        if ($type === 'array' && is_array($value)) {
            if (isset($schema['minItems']) && count($value) < (int) $schema['minItems']) {
                $errors[$path][] = $this->message('items_min');
            }
            if (isset($schema['maxItems']) && count($value) > (int) $schema['maxItems']) {
                $errors[$path][] = $this->message('items_max');
            }
            if (is_array($schema['items'] ?? null)) {
                foreach ($value as $index => $item) {
                    $this->validateSchema($item, $schema['items'], "{$path}.{$index}", $errors);
                }
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @param  array<string, list<string>>  $errors
     */
    private function validateHeadingHierarchy(array $blocks, array &$errors, string $path = 'document.blocks', bool &$hasH1 = false): void
    {
        foreach ($blocks as $index => $block) {
            $blockPath = "{$path}.{$index}";
            if (($block['type'] ?? null) === 'content.heading' && data_get($block, 'props.level') === 1) {
                if ($hasH1) {
                    $errors["{$blockPath}.props.level"][] = $this->message('multiple_h1');
                }
                $hasH1 = true;
            }
            $children = is_array($block['children'] ?? null) ? $block['children'] : [];
            $this->validateHeadingHierarchy($children, $errors, "{$blockPath}.children", $hasH1);
        }
    }

    /** @param array<string, list<string>> $errors */
    private function rejectExecutablePayload(mixed $value, string $path, array &$errors): void
    {
        if (is_string($value) && preg_match('/(<\s*script\b|<\?php|@php\b|\{!!|\{\{|javascript\s*:|on[a-z]+\s*=|data\s*:\s*text\/html)/i', $value) === 1) {
            $errors[$path][] = $this->message('executable_payload');
        }
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $this->rejectExecutablePayload($child, $path.'.'.$key, $errors);
            }
        }
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @param  list<string>  $allowed
     * @param  array<string, list<string>>  $errors
     */
    private function rejectUnknownKeys(array $value, array $allowed, string $path, array &$errors): void
    {
        foreach (array_diff(array_keys($value), $allowed) as $key) {
            $errors["{$path}.{$key}"][] = $this->message('unknown_field');
        }
    }

    /**
     * @param  array<string, list<string>>  $dependencies
     * @param  array<string, string>  $paths
     */
    private function collectDependencies(mixed $value, string $owner, string $path, array &$dependencies, array &$paths): void
    {
        if (! is_array($value)) {
            return;
        }
        foreach ($value as $key => $child) {
            $childPath = $path.'.'.$key;
            if ($key === 'sourceBlockId' && is_string($child)) {
                $dependencies[$owner][] = $child;
                $paths[$owner.'>'.$child] = $childPath;
            } else {
                $this->collectDependencies($child, $owner, $childPath, $dependencies, $paths);
            }
        }
    }

    /**
     * @param  array<string, true>  $ids
     * @param  array<string, list<string>>  $dependencies
     * @param  array<string, string>  $paths
     * @param  array<string, list<string>>  $errors
     */
    private function validateDependencyGraph(array $ids, array $dependencies, array $paths, array &$errors): void
    {
        foreach ($dependencies as $owner => $targets) {
            foreach ($targets as $target) {
                if (! isset($ids[$target])) {
                    $errors[$paths[$owner.'>'.$target] ?? 'document.blocks'][] = $this->message('unknown_reference');
                }
            }
        }
        $visiting = [];
        $visited = [];
        $walk = function (string $node) use (&$walk, &$visiting, &$visited, $dependencies, $paths, &$errors): void {
            if (isset($visited[$node])) {
                return;
            }
            $visiting[$node] = true;
            foreach ($dependencies[$node] ?? [] as $target) {
                if (isset($visiting[$target])) {
                    $errors[$paths[$node.'>'.$target] ?? 'document.blocks'][] = $this->message('cycle');
                } else {
                    $walk($target);
                }
            }
            unset($visiting[$node]);
            $visited[$node] = true;
        };
        foreach (array_keys($ids) as $id) {
            $walk($id);
        }
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, mixed>  $values
     * @return array<TKey, mixed>
     */
    private function sortRecursively(array $values): array
    {
        ksort($values);
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursively($value);
            }
        }

        return $values;
    }

    private function message(string $key): string
    {
        $message = __("page_builder.validation.{$key}");

        return is_string($message) ? $message : $key;
    }
}
