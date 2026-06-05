const fs = require('fs');
const path = require('path');
const vm = require('vm');

const filePath = path.join(__dirname, 'resources/views/manager/events.blade.php');
const content = fs.readFileSync(filePath, 'utf8');

// Regex to find all <script> blocks
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let allScriptContent = '';
let index = 1;

console.log("Extracting scripts...");

while ((match = scriptRegex.exec(content)) !== null) {
    const code = match[1];
    // Check if the script is not just a document.write or simple translation
    if (code.trim() && !code.includes('document.write')) {
        console.log(`Script Block #${index} (${code.length} chars)`);
        try {
            // Test compiling this script block
            new vm.Script(code, { filename: `block_${index}.js` });
        } catch (err) {
            console.error(`❌ Syntax Error in Script Block #${index}:`, err.message);
            console.error("Context near error:");
            const lines = code.split('\n');
            const errorLineNum = err.stack.match(/block_\d+\.js:(\d+)/)?.[1];
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

console.log("Compiling all scripts combined...");
try {
    new vm.Script(allScriptContent, { filename: 'combined.js' });
    console.log("✅ All javascript code compiles successfully without syntax errors!");
} catch (err) {
    console.error("❌ Syntax Error in combined script:", err.message);
}
