import { createReadStream, existsSync, statSync } from 'node:fs';
import { createServer } from 'node:http';
import { extname, isAbsolute, join, normalize, resolve, sep } from 'node:path';

const [rootArgument, portArgument = '8000', basePathArgument = '/'] = process.argv.slice(2);

if (!rootArgument) {
  throw new Error('Usage: node scripts/serve-spa.mjs ROOT [PORT] [BASE_PATH]');
}

const root = resolve(rootArgument);
const port = Number.parseInt(portArgument, 10);
const basePath = `/${basePathArgument.replace(/^\/+|\/+$/g, '')}/`.replace('//', '/');

if (!existsSync(root) || !statSync(root).isDirectory()) {
  throw new Error(`Static root does not exist: ${root}`);
}
if (!Number.isInteger(port) || port < 1 || port > 65535) {
  throw new Error(`Invalid port: ${portArgument}`);
}
if (isAbsolute(basePathArgument) && !basePathArgument.startsWith('/')) {
  throw new Error(`Invalid base path: ${basePathArgument}`);
}

const contentTypes = new Map([
  ['.css', 'text/css; charset=utf-8'],
  ['.html', 'text/html; charset=utf-8'],
  ['.ico', 'image/x-icon'],
  ['.js', 'text/javascript; charset=utf-8'],
  ['.json', 'application/json; charset=utf-8'],
  ['.png', 'image/png'],
  ['.svg', 'image/svg+xml'],
  ['.webp', 'image/webp'],
  ['.woff', 'font/woff'],
  ['.woff2', 'font/woff2'],
]);

const indexFile = join(root, 'index.html');

createServer((request, response) => {
  if (request.method !== 'GET' && request.method !== 'HEAD') {
    response.writeHead(405, { Allow: 'GET, HEAD' });
    response.end();
    return;
  }

  const requestUrl = new URL(request.url ?? '/', 'http://127.0.0.1');
  if (!requestUrl.pathname.startsWith(basePath)) {
    response.writeHead(404);
    response.end();
    return;
  }

  const relativePath = decodeURIComponent(requestUrl.pathname.slice(basePath.length));
  const candidate = resolve(root, normalize(relativePath));
  const isInsideRoot = candidate === root || candidate.startsWith(`${root}${sep}`);
  const file = isInsideRoot && existsSync(candidate) && statSync(candidate).isFile()
    ? candidate
    : indexFile;

  if (!existsSync(file)) {
    response.writeHead(404);
    response.end();
    return;
  }

  response.writeHead(200, {
    'Cache-Control': 'no-store',
    'Content-Type': contentTypes.get(extname(file).toLowerCase()) ?? 'application/octet-stream',
    'X-Content-Type-Options': 'nosniff',
  });
  if (request.method === 'HEAD') {
    response.end();
    return;
  }
  createReadStream(file).pipe(response);
}).listen(port, '127.0.0.1', () => {
  process.stdout.write(`Serving ${root} at http://127.0.0.1:${port}${basePath}\n`);
});
