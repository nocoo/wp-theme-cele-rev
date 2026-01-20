/**
 * CeleRev CSS Minify Script
 * Uses csso for fast, professional CSS minification
 */

import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { minify as minifyCss } from 'csso';

const SOURCE_DIR = join(import.meta.dir, '..');
const CSS_FILES = [
  'celerev/style.css',
  'celerev/rtl.css',
  'celerev/styles/rtl.css',
  'celerev/styles/admin.css',
  'celerev/styles/customizer.css',
  'celerev/styles/editor-style.css',
];

function minify() {
  console.log('Minifying CSS files...\n');

  for (const file of CSS_FILES) {
    const inputPath = join(SOURCE_DIR, file);
    const outputPath = inputPath.replace('.css', '.min.css');

    try {
      const css = readFileSync(inputPath, 'utf-8');
      const result = minifyCss(css);
      writeFileSync(outputPath, result.css);
      const savings = ((1 - result.css.length / css.length) * 100).toFixed(1);
      console.log(`  ✓ ${file} -> ${file.replace('.css', '.min.css')} (${savings}% smaller)`);
    } catch (error) {
      console.error(`  ✗ Error minifying ${file}:`, error.message);
    }
  }

  console.log('\nCSS minification complete!');
}

minify();
