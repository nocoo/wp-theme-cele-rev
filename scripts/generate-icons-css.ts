#!/usr/bin/env bun
/**
 * Generate CSS for SVG icons
 * Creates lightweight CSS using SVG background images
 */

import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const ICONS_DIR = join(import.meta.dir, '../celerev/assets/icons');
const OUTPUT_FILE = join(import.meta.dir, '../celerev/assets/icons/icons.css');

// SVG to data URI
function svgToDataUri(svg: string): string {
  const cleaned = svg
    .replace(/<!--.*?-->/gs, '')
    .replace(/\s+/g, ' ')
    .trim();
  const base64 = Buffer.from(cleaned).toString('base64');
  return `url('data:image/svg+xml;base64,${base64}')`;
}

/**
 * Map CSS class names to SVG filenames
 * Handles special cases where PHP uses different names than Font Awesome
 */
const iconMappings: Record<string, { file: string; type: 'solid' | 'regular' | 'brands' }> = {
  // Solid icons
  'angle-down': { file: 'angle-down', type: 'solid' },
  'arrow-up': { file: 'arrow-up', type: 'solid' },
  'comment': { file: 'comment', type: 'solid' },
  'edit': { file: 'edit', type: 'solid' },
  'folder': { file: 'folder', type: 'solid' },
  'phone': { file: 'phone', type: 'solid' },
  'podcast': { file: 'podcast', type: 'solid' },
  'reply': { file: 'reply', type: 'solid' },
  'rss': { file: 'rss', type: 'solid' },
  'search': { file: 'search', type: 'solid' },
  'tag': { file: 'tag', type: 'solid' },

  // Regular icons
  'envelope': { file: 'envelope', type: 'regular' },

  // Brand icons (with special mappings for PHP compatibility)
  '500px': { file: '500px', type: 'brands' },
  'amazon': { file: 'amazon', type: 'brands' },
  'artstation': { file: 'artstation', type: 'brands' },
  'bandcamp': { file: 'bandcamp', type: 'brands' },
  'behance': { file: 'behance', type: 'brands' },
  'bitbucket': { file: 'bitbucket', type: 'brands' },
  'codepen': { file: 'codepen', type: 'brands' },
  'delicious': { file: 'delicious', type: 'brands' },
  'deviantart': { file: 'deviantart', type: 'brands' },
  'digg': { file: 'digg', type: 'brands' },
  'discord': { file: 'discord', type: 'brands' },
  'dribbble': { file: 'dribbble', type: 'brands' },
  'etsy': { file: 'etsy', type: 'brands' },
  'facebook': { file: 'facebook', type: 'brands' },
  'flickr': { file: 'flickr', type: 'brands' },
  'foursquare': { file: 'foursquare', type: 'brands' },
  'github': { file: 'github', type: 'brands' },
  'goodreads': { file: 'goodreads', type: 'brands' },
  'hacker-news': { file: 'hacker-news', type: 'brands' },
  'instagram': { file: 'instagram', type: 'brands' },
  'linkedin': { file: 'linkedin', type: 'brands' },
  'medium': { file: 'medium', type: 'brands' },
  'meetup': { file: 'meetup', type: 'brands' },
  'mixcloud': { file: 'mixcloud', type: 'brands' },
  'odnoklassniki': { file: 'odnoklassniki', type: 'brands' }, // ok-ru in PHP
  'orcid': { file: 'orcid', type: 'brands' },
  'patreon': { file: 'patreon', type: 'brands' },
  'paypal': { file: 'paypal', type: 'brands' },
  'pinterest': { file: 'pinterest', type: 'brands' },
  'quora': { file: 'quora', type: 'brands' },
  'ravelry': { file: 'ravelry', type: 'brands' },
  'reddit': { file: 'reddit', type: 'brands' },
  'researchgate': { file: 'researchgate', type: 'brands' },
  'skype': { file: 'skype', type: 'brands' },
  'slack': { file: 'slack', type: 'brands' },
  'slideshare': { file: 'slideshare', type: 'brands' },
  'snapchat': { file: 'snapchat', type: 'brands' },
  'soundcloud': { file: 'soundcloud', type: 'brands' },
  'spotify': { file: 'spotify', type: 'brands' },
  'stack-overflow': { file: 'stack-overflow', type: 'brands' },
  'steam': { file: 'steam', type: 'brands' },
  'strava': { file: 'strava', type: 'brands' },
  'telegram': { file: 'telegram', type: 'brands' },
  'tencent-weibo': { file: 'tencent-weibo', type: 'brands' },
  'tumblr': { file: 'tumblr', type: 'brands' },
  'twitch': { file: 'twitch', type: 'brands' },
  'untappd': { file: 'untappd', type: 'brands' },
  'vimeo': { file: 'vimeo', type: 'brands' },
  'vk': { file: 'vk', type: 'brands' },
  'weibo': { file: 'weibo', type: 'brands' },
  'weixin': { file: 'weixin', type: 'brands' }, // wechat in PHP
  'whatsapp': { file: 'whatsapp', type: 'brands' },
  'x-twitter': { file: 'x-twitter', type: 'brands' }, // twitter in PHP
  'xing': { file: 'xing', type: 'brands' },
  'yahoo': { file: 'yahoo', type: 'brands' },
  'yelp': { file: 'yelp', type: 'brands' },
  'youtube': { file: 'youtube', type: 'brands' },
  'qq': { file: 'qq', type: 'brands' },
  'get-pocket': { file: 'get-pocket', type: 'brands' }, // pocket in PHP
};

let css = `/**
 * CeleRev Icons
 * Lightweight SVG-based icon system
 * Generated from Font Awesome SVGs
 */

`;

// Helper to generate CSS for an icon
function generateIconClass(className: string, dataUri: string, type: 'solid' | 'regular' | 'brands'): string {
  const prefix = type === 'solid' ? 'fas' : type === 'regular' ? 'far' : 'fab';

  return `
/* ${prefix} fa-${className} */
.${prefix}.fa-${className}::before,
.${prefix} .fa-${className}::before {
  content: '';
  display: inline-block;
  width: 1em;
  height: 1em;
  background-image: ${dataUri};
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  vertical-align: middle;
}

.fa-${className}::before {
  background-image: ${dataUri};
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}
`;
}

// Generate CSS for all icons
let processedCount = 0;
for (const [className, { file, type }] of Object.entries(iconMappings)) {
  const svgPath = join(ICONS_DIR, type, `${file}.svg`);
  try {
    const svg = readFileSync(svgPath, 'utf-8');
    const dataUri = svgToDataUri(svg);
    css += generateIconClass(className, dataUri, type);
    processedCount++;
  } catch (e) {
    console.error(`❌ Error reading ${svgPath}:`, e);
  }
}

// Write CSS file
writeFileSync(OUTPUT_FILE, css, 'utf-8');
console.log(`✅ Generated ${OUTPUT_FILE}`);
console.log(`   ${processedCount} icons processed`);
