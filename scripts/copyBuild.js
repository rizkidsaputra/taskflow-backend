/**
 * After `npm run build`, copy `build/` into ../taskflow-backend/public
 */
const fs = require('fs');
const path = require('path');

function copyRecursive(src, dest) {
  if (!fs.existsSync(src)) return;
  if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
  const entries = fs.readdirSync(src, { withFileTypes: true });
  for (const e of entries) {
    const s = path.join(src, e.name);
    const d = path.join(dest, e.name);
    if (e.isDirectory()) {
      copyRecursive(s, d);
    } else {
      fs.copyFileSync(s, d);
    }
  }
}

const feRoot = process.cwd();
const buildDir = path.join(feRoot, 'build');
const bePublic = path.resolve(feRoot, '../taskflow-backend/public');

try {
  // Clean destination first
  if (fs.existsSync(bePublic)) {
    fs.rmSync(bePublic, { recursive: true, force: true });
  }
  fs.mkdirSync(bePublic, { recursive: true });
  copyRecursive(buildDir, bePublic);
  console.log('Copied build ->', bePublic);
} catch (err) {
  console.error('Failed to copy build:', err);
  process.exitCode = 1;
}
