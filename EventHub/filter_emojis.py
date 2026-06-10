import re

ignored_emojis = {'✕', '✓ ✕', '✓', '★', '★ ★ ★ ★ ★', '✕ ✕ ✕'}

output_lines = []
with open(r'c:\Users\Ahmed\Desktop\EventHub\EventHub\emojis_found.txt', 'r', encoding='utf-8') as f:
    for line in f:
        parts = line.split(' -> ')
        if len(parts) > 0:
            left = parts[0].split(': ')
            if len(left) > 1:
                emojis = left[1].strip()
                # If the emojis matched are not in our ignored set
                matched_emojis = [e for e in emojis.split() if e not in ignored_emojis]
                if matched_emojis:
                    output_lines.append(line)

with open(r'c:\Users\Ahmed\Desktop\EventHub\EventHub\emojis_to_fix.txt', 'w', encoding='utf-8') as f_out:
    f_out.writelines(output_lines)

print(f"Filtered list contains {len(output_lines)} lines.")
