import os
import re

directory = r'c:\Users\Ahmed\Desktop\EventHub\EventHub\resources\views'

# Define regex patterns for emojis and miscellaneous symbols
# Excluding ✕ (multiplication sign) and common math/punctuation symbols
emoji_pattern = re.compile(r'[\u2600-\u27BF\U0001F300-\U0001F9FF\U0001FA70-\U0001FAFF]')

output_lines = []

for root, _, files in os.walk(directory):
    for f in files:
        if f.endswith('.blade.php'):
            filepath = os.path.join(root, f)
            relpath = os.path.relpath(filepath, directory)
            
            # Try utf-8 first, fallback to windows-1256
            encodings = ['utf-8', 'windows-1256', 'latin-1']
            content = None
            for encoding in encodings:
                try:
                    with open(filepath, 'r', encoding=encoding) as file:
                        lines = file.readlines()
                    content = lines
                    break
                except UnicodeDecodeError:
                    continue
            
            if content is None:
                output_lines.append(f"Could not read {relpath}\n")
                continue
                
            for idx, line in enumerate(lines, 1):
                matches = emoji_pattern.findall(line)
                if matches:
                    output_lines.append(f"{relpath}:{idx}: {' '.join(matches)} -> {line.strip()}\n")

with open(r'c:\Users\Ahmed\Desktop\EventHub\EventHub\emojis_found.txt', 'w', encoding='utf-8') as f_out:
    f_out.writelines(output_lines)

print(f"Done! Found {len(output_lines)} lines with emojis and wrote them to emojis_found.txt")
