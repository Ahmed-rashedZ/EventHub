const fs = require('fs');
const path = require('path');
const vm = require('vm');

const filesToCheck = [
    'resources/views/manager/events.blade.php',
    'resources/views/admin/events.blade.php',
    'resources/views/company/events.blade.php',
    'resources/views/sponsor/events.blade.php'
];

filesToCheck.forEach(relPath => {
    const filePath = path.join(__dirname, relPath);
    if (!fs.existsSync(filePath)) {
        console.log(`Skipping non-existent file: ${relPath}`);
        return;
    }
    
    const content = fs.readFileSync(filePath, 'utf8');

    // Regex to find all <script> blocks
    const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
    let match;
    let allScriptContent = '';
    let index = 1;

    console.log(`\nChecking JS in ${relPath}...`);

    while ((match = scriptRegex.exec(content)) !== null) {
        const code = match[1];
        // Check if the script is not just a document.write or simple translation
        if (code.trim() && !code.includes('document.write')) {
            try {
                // Test compiling this script block
                new vm.Script(code, { filename: `${path.basename(relPath)}_block_${index}.js` });
            } catch (err) {
                console.error(`❌ Syntax Error in ${relPath} Script Block #${index}:`, err.message);
                console.error("Context near error:");
                const lines = code.split('\n');
                const errorLineNum = err.stack.match(/_block_\d+\.js:(\d+)/)?.[1];
                if (errorLineNum) {
                    const lineIdx = parseInt(errorLineNum) - 1;
                    for (let i = Math.max(0, lineIdx - 5); i <= Math.min(lines.length - 1, lineIdx + 5); i++) {
                        console.error(`${i + 1}: ${lines[i]}`);
                    }
                } else {
                    console.error(code.substring(0, 500));
                }
                process.exit(1);
            }
            allScriptContent += code + '\n';
            index++;
        }
    }

    try {
        new vm.Script(allScriptContent, { filename: `${path.basename(relPath)}_combined.js` });
        console.log(`✅ All JS in ${relPath} compiles successfully!`);
    } catch (err) {
        console.error(`❌ Syntax Error in combined script for ${relPath}:`, err.message);
        process.exit(1);
    }
});

console.log("\nDone checking all files! Everything is syntactically correct.");
