<?php

return [
    'created' => 'Page created.',
    'updated' => 'Page metadata updated.',
    'draft_saved' => 'Page Builder draft saved.',
    'slug_taken' => 'This slug is already used for the selected locale.',
    'validation' => [
        'payload_too_large' => 'The document exceeds the allowed payload size.', 'schema_version' => 'The PageDocument version is not supported.',
        'ulid' => 'The value must be a valid ULID.', 'blocks_list' => 'Blocks must be a sequential array.', 'block_object' => 'A block must be an object.',
        'block_id' => 'The block ID is invalid.', 'duplicate_id' => 'The block ID is duplicated.', 'unknown_block' => 'The block type is not registered.',
        'block_version' => 'The block version is not supported.', 'invalid_parent' => 'The block is not allowed at the root.', 'invalid_child' => 'The child block is not allowed by its parent.',
        'children_list' => 'Children must be a sequential array.', 'children_not_allowed' => 'This block does not allow children.', 'too_many_blocks' => 'The document has too many blocks.',
        'too_deep' => 'The block tree exceeds the allowed depth.', 'object' => 'The value must be an object.', 'required' => 'This field is required.',
        'unknown_field' => 'This field is not allowlisted.', 'type' => 'The value type is invalid.', 'enum' => 'The value is not allowlisted.', 'max_length' => 'The string exceeds the allowed length.',
        'executable_payload' => 'Blade, PHP, JavaScript, and event handlers are not allowed.', 'unknown_reference' => 'The binding references a missing block.',
        'cycle' => 'The bindings create a block reference cycle.', 'migration_missing' => 'A sequential migration is missing for this block version.',
    ],
];
