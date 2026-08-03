import { readdir, stat } from 'node:fs/promises';
import { extname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const buildDirectory = fileURLToPath(new URL('../public/build/', import.meta.url));
const limits = new Map([
  // P19 self-hosts the owner-approved Bootstrap, jQuery and Font Awesome vendor runtime.
  ['.css', 320 * 1024],
  ['.js', 176 * 1024],
  ['.avif', 500 * 1024],
  ['.webp', 500 * 1024],
  ['.png', 500 * 1024],
  ['.jpg', 500 * 1024],
  ['.jpeg', 500 * 1024],
]);

const failures = [];

async function inspect(directory) {
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    const file = join(directory, entry.name);

    if (entry.isDirectory()) {
      await inspect(file);
      continue;
    }

    const limit = limits.get(extname(entry.name).toLowerCase());

    if (limit === undefined) {
      continue;
    }

    const { size } = await stat(file);

    if (size > limit) {
      failures.push(`${relative(buildDirectory, file)}: ${size} bytes > ${limit} bytes`);
    }
  }
}

await inspect(buildDirectory);

if (failures.length > 0) {
  console.error('Public performance budget exceeded:\n' + failures.join('\n'));
  process.exitCode = 1;
} else {
  console.log('Public performance budget passed.');
}
