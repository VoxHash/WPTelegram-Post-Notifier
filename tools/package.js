const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Create dist directory
const distDir = path.join(__dirname, '..', 'dist');
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}

// Plugin files to include
const pluginFiles = [
    'wp-telegram-post-notifier.php',
    'uninstall.php',
    'includes/',
    'admin/',
    'public/',
    'vendor/',
    'languages/',
    'assets/',
    'README.md',
    'CHANGELOG.md',
    'LICENSE',
];

// Create temporary directory
const tempDir = path.join(distDir, 'temp');
if (fs.existsSync(tempDir)) {
    fs.rmSync(tempDir, { recursive: true });
}
fs.mkdirSync(tempDir, { recursive: true });

// Copy plugin files
console.log('Copying plugin files...');
pluginFiles.forEach(file => {
    const srcPath = path.join(__dirname, '..', file);
    const destPath = path.join(tempDir, file);
    
    if (fs.existsSync(srcPath)) {
        if (fs.statSync(srcPath).isDirectory()) {
            fs.cpSync(srcPath, destPath, { recursive: true });
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
        console.log(`✓ ${file}`);
    } else {
        console.log(`⚠ ${file} not found`);
    }
});

// Create zip file
const zipPath = path.join(distDir, 'wp-telegram-post-notifier.zip');
console.log('Creating zip file...');

try {
    execSync(`cd "${tempDir}" && zip -r "${zipPath}" .`, { stdio: 'inherit' });
    console.log(`✓ Created ${zipPath}`);
} catch (error) {
    console.error('Failed to create zip file:', error.message);
    process.exit(1);
}

// Clean up
fs.rmSync(tempDir, { recursive: true });
console.log('✓ Package created successfully!');
