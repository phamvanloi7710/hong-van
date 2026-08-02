import { cp, lstat, mkdir, readdir, readFile, rename, rm } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ADMIN_BASE_HREF = '/admin/';
const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const adminDirectory = path.resolve(scriptDirectory, '..');
const repositoryDirectory = path.resolve(adminDirectory, '..');
const sourceDirectory = path.join(adminDirectory, 'dist', 'hongvan-admin', 'browser');
const publicAdminDirectory = path.join(repositoryDirectory, 'BackEnd', 'public', 'admin');
const targetDirectory = path.join(publicAdminDirectory, 'browser');
const stagingDirectory = path.join(
  publicAdminDirectory,
  `.browser-sync-${process.pid}-${Date.now()}`,
);

assertSafeTarget();
await assertSafeFilesystem();
await validateBuild(sourceDirectory);

try {
  await mkdir(publicAdminDirectory, { recursive: true });
  await rm(stagingDirectory, { force: true, recursive: true });
  await cp(sourceDirectory, stagingDirectory, { recursive: true });
  await validateBuild(stagingDirectory);
  await rm(targetDirectory, { force: true, recursive: true });
  await rename(stagingDirectory, targetDirectory);
} catch (error) {
  await rm(stagingDirectory, { force: true, recursive: true });
  throw error;
}

const deployedFiles = await listFiles(targetDirectory);
console.log(
  `Synced ${deployedFiles.length} Angular files to ${path.relative(repositoryDirectory, targetDirectory)}.`,
);

function assertSafeTarget() {
  const expectedTarget = path.resolve(repositoryDirectory, 'BackEnd', 'public', 'admin', 'browser');
  const resolvedTarget = path.resolve(targetDirectory);
  const resolvedPublicAdmin = `${path.resolve(publicAdminDirectory)}${path.sep}`;

  if (resolvedTarget !== expectedTarget) {
    throw new Error(`Refusing to sync to unexpected target: ${resolvedTarget}`);
  }

  if (!`${resolvedTarget}${path.sep}`.startsWith(resolvedPublicAdmin)) {
    throw new Error(`Target escapes Laravel public/admin: ${resolvedTarget}`);
  }

  if (path.basename(resolvedTarget) !== 'browser') {
    throw new Error(`Target must end with the guarded browser directory: ${resolvedTarget}`);
  }
}

async function assertSafeFilesystem() {
  for (const directory of [publicAdminDirectory, targetDirectory]) {
    const stats = await lstat(directory).catch(() => null);

    if (stats?.isSymbolicLink()) {
      throw new Error(`Refusing to sync through a symbolic link: ${directory}`);
    }

    if (stats !== null && !stats.isDirectory()) {
      throw new Error(`Expected a directory at guarded sync path: ${directory}`);
    }
  }
}

async function validateBuild(directory) {
  const directoryStats = await lstat(directory).catch(() => null);

  if (directoryStats === null || !directoryStats.isDirectory() || directoryStats.isSymbolicLink()) {
    throw new Error(`Angular browser output is missing or unsafe: ${directory}`);
  }

  const files = await listFiles(directory);
  const relativeFiles = new Set(
    files.map((file) => path.relative(directory, file).replaceAll('\\', '/')),
  );

  if (!relativeFiles.has('index.html')) {
    throw new Error(`Angular browser output has no index.html: ${directory}`);
  }

  const sourceMaps = [...relativeFiles].filter((file) => file.endsWith('.map'));

  if (sourceMaps.length > 0) {
    throw new Error(`Production source maps are not allowed: ${sourceMaps.join(', ')}`);
  }

  const indexHtml = await readFile(path.join(directory, 'index.html'), 'utf8');

  if (!indexHtml.includes(`<base href="${ADMIN_BASE_HREF}">`)) {
    throw new Error(`index.html must use base href ${ADMIN_BASE_HREF}`);
  }

  const localAssets = extractLocalAssets(indexHtml);

  if (localAssets.length === 0) {
    throw new Error('index.html does not reference an Angular JavaScript or stylesheet asset.');
  }

  const incorrectlyDeployedAssets = localAssets.filter(
    (asset) => /\.(?:css|js)(?:[?#]|$)/i.test(asset) && !asset.startsWith(ADMIN_BASE_HREF),
  );

  if (incorrectlyDeployedAssets.length > 0) {
    throw new Error(
      `JavaScript and stylesheet assets must deploy under ${ADMIN_BASE_HREF}: ${incorrectlyDeployedAssets.join(', ')}`,
    );
  }

  const missingAssets = localAssets.filter((asset) => {
    const relativeAsset = asset.startsWith(ADMIN_BASE_HREF)
      ? asset.slice(ADMIN_BASE_HREF.length)
      : asset.replace(/^\.\//, '');

    return !relativeFiles.has(relativeAsset);
  });

  if (missingAssets.length > 0) {
    throw new Error(`index.html references missing build assets: ${missingAssets.join(', ')}`);
  }
}

function extractLocalAssets(indexHtml) {
  const assets = [];
  const attributePattern = /(?:href|src)="([^"]+)"/g;

  for (const match of indexHtml.matchAll(attributePattern)) {
    const value = match[1];

    if (
      value === ADMIN_BASE_HREF ||
      value.startsWith('data:') ||
      value.startsWith('http://') ||
      value.startsWith('https://') ||
      value.startsWith('//')
    ) {
      continue;
    }

    assets.push(value.split(/[?#]/, 1)[0]);
  }

  return assets;
}

async function listFiles(directory) {
  const entries = await readdir(directory, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const entryPath = path.join(directory, entry.name);

    if (entry.isSymbolicLink()) {
      throw new Error(`Symbolic links are not allowed in Angular build output: ${entryPath}`);
    }

    if (entry.isDirectory()) {
      files.push(...(await listFiles(entryPath)));
      continue;
    }

    if (entry.isFile()) {
      files.push(entryPath);
    }
  }

  return files;
}
