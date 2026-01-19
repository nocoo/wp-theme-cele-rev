/**
 * CeleRev Theme Build Script
 * Uses Bun's native bundler for fast builds
 * Output: celerev/js/build/
 */

import { existsSync, mkdirSync, readFileSync, writeFileSync, rmSync } from 'node:fs';
import { join } from 'node:path';
import { mkdtempSync } from 'node:fs';

const SOURCE_DIR = join(import.meta.dir, 'celerev/js');
const OUTPUT_DIR = join(import.meta.dir, 'celerev/js/build');

// Ensure output directory exists
if (!existsSync(OUTPUT_DIR)) {
    mkdirSync(OUTPUT_DIR, { recursive: true });
}

async function build() {
    console.log('Building CeleRev theme...\n');

    // Build production.js (without fitvids - optimization #5)
    console.log(`Bundling production.js...`);
    const functionsSrc = readFileSync(join(SOURCE_DIR, 'functions.js'), 'utf-8');

    // Create production input directly in memory
    const productionInput = join(SOURCE_DIR, 'production.js');
    writeFileSync(productionInput, functionsSrc);

    const unbundled = await Bun.build({
        entrypoints: [productionInput],
        external: [],
        minify: false,
        target: 'browser',
    });

    if (unbundled.success) {
        const content = await unbundled.outputs[0].text();
        writeFileSync(join(OUTPUT_DIR, 'production.js'), content);
        console.log(`  -> production.js (${(content.length / 1024).toFixed(2)} KB)`);

        // Minified
        const minified = await Bun.build({
          entrypoints: [productionInput],
          external: [],
          minify: true,
          target: 'browser',
        });
        if (minified.success) {
          const minContent = await minified.outputs[0].text();
          writeFileSync(join(OUTPUT_DIR, 'production.min.js'), minContent);
          console.log(`  -> production.min.js (${(minContent.length / 1024).toFixed(2)} KB)`);
        }
    }

    // Build customizer.js (standalone)
    console.log(`\nBundling customizer.js...`);
    const customizerUnbundled = await Bun.build({
        entrypoints: [join(SOURCE_DIR, 'customizer.js')],
        external: ['jquery', 'wp'],
        minify: false,
        target: 'browser',
    });

    if (customizerUnbundled.success) {
        const content = await customizerUnbundled.outputs[0].text();
        writeFileSync(join(OUTPUT_DIR, 'customizer.js'), content);
        console.log(`  -> customizer.js (${(content.length / 1024).toFixed(2)} KB)`);

        const minified = await Bun.build({
          entrypoints: [join(SOURCE_DIR, 'customizer.js')],
          external: ['jquery', 'wp'],
          minify: true,
          target: 'browser',
        });
        if (minified.success) {
          const minContent = await minified.outputs[0].text();
          writeFileSync(join(OUTPUT_DIR, 'customizer.min.js'), minContent);
          console.log(`  -> customizer.min.js (${(minContent.length / 1024).toFixed(2)} KB)`);
        }
    }

    // Minify postMessage.js
    const postMessageSrc = join(SOURCE_DIR, 'postMessage.js');
    const postMessageContent = readFileSync(postMessageSrc, 'utf-8');
    const minifiedPostMessage = await minifyJs(postMessageContent);
    writeFileSync(join(OUTPUT_DIR, 'postMessage.min.js'), minifiedPostMessage);
    console.log(`\npostMessage.js -> postMessage.min.js (${(minifiedPostMessage.length / 1024).toFixed(2)} KB)`);

    console.log('\nBuild complete!');
}

async function minifyJs(code: string): Promise<string> {
    try {
        // Create temp dir and file - optimization #7
        const tempDir = mkdtempSync('bun_minify_');
        const path = join(tempDir, 'minify.js');
        writeFileSync(path, code);
        const result = await Bun.build({
          entrypoints: [path],
          minify: true,
          target: 'browser',
        });
        rmSync(path, { force: true });
        rmSync(tempDir, { recursive: true, force: true });
        if (result.success) {
          return result.outputs[0].text();
        }
    } catch {}
    return code;
}

build().catch(console.error);
