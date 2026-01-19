import { readFileSync, writeFileSync } from 'node:fs';

const files = [
  'celerev/style.css',
  'celerev/rtl.css',
  'celerev/styles/editor-style.css',
];

for (const file of files) {
  const css = readFileSync(file, 'utf-8');
  // Simple minification
  const minified = css
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\s+/g, ' ')
    .replace(/\s*([{}:;,>+~])\s*/g, '$1')
    .replace(/;\}/g, '}')
    .trim();
  writeFileSync(file.replace('.css', '.min.css'), minified);
  console.log(`Minified ${file} -> ${file.replace('.css', '.min.css')}`);
}
