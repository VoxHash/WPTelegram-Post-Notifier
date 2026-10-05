const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.join(__dirname, '..');
const distDir = path.join(root, 'dist');
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}

// Only ship runtime plugin files (not Composer/npm toolchains).
const pluginFiles = [
    'wp-telegram-post-notifier.php',
    'uninstall.php',
    'includes/',
    'admin/',
    'public/',
    'vendor/action-scheduler/',
    'languages/',
    'assets/',
    'README.md',
    'CHANGELOG.md',
    'LICENSE',
];

const tempDir = path.join(distDir, 'temp');
if (fs.existsSync(tempDir)) {
    fs.rmSync(tempDir, { recursive: true });
}
fs.mkdirSync(tempDir, { recursive: true });

const skipDirs = new Set(['node_modules', 'src', '.git']);

function copyRecursive(src, dest) {
    const stat = fs.statSync(src);
    if (stat.isDirectory()) {
        const base = path.basename(src);
        if (skipDirs.has(base)) {
            return;
        }
        fs.mkdirSync(dest, { recursive: true });
        for (const entry of fs.readdirSync(src)) {
            if (skipDirs.has(entry)) {
                continue;
            }
            // Do not ship TypeScript sources or empty build placeholders incorrectly
            copyRecursive(path.join(src, entry), path.join(dest, entry));
        }
        return;
    }
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
}

console.log('Copying plugin files...');
pluginFiles.forEach((file) => {
    const srcPath = path.join(root, file);
    const destPath = path.join(tempDir, file);

    if (!fs.existsSync(srcPath)) {
        console.log(`⚠ ${file} not found`);
        return;
    }

    copyRecursive(srcPath, destPath);
    console.log(`✓ ${file}`);
});

const zipPath = path.join(distDir, 'wp-telegram-post-notifier.zip');
console.log('Creating zip file...');

try {
    execSync(`cd "${tempDir}" && zip -r "${zipPath}" .`, { stdio: 'inherit' });
    console.log(`✓ Created ${zipPath}`);
} catch (error) {
    console.error('Failed to create zip file:', error.message);
    process.exit(1);
}

fs.rmSync(tempDir, { recursive: true });
console.log('✓ Package created successfully!');
