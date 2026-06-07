import os
import re

directory = r'c:\Users\Ahmed\Desktop\EventHub\EventHub\resources\views'
pattern = re.compile(r'const renderItem=a=>`<div style="display:flex;align-items:center;gap:10px;background:rgba\(34,211,238,0\.04\);border:1px solid rgba\(34,211,238,0\.12\);border-radius:10px;padding:8px 14px;margin(?:-left)?:(?:0 )?8px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba\(34,211,238,0\.1\);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0\.75rem;font-weight:600;">\$\{a\.start_time\}</span><span style="color:#64748b;font-size:0\.7rem;">→</span><span style="background:rgba\(245,158,11,0\.1\);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0\.75rem;font-weight:600;">\$\{a\.end_time\}</span></div><div style="flex:1;font-size:0\.85rem;color:#e2e8f0;font-weight:500;">\$\{a\.title\}</div></div>`;')

replacement = r'const renderItem=a=>`<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;"><div style="display:flex;align-items:center;gap:10px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>${a.description ? \`<div style="font-size:0.75rem;color:#94a3b8;margin-inline-start:126px;">${a.description}</div>\` : \'\'}</div>`;'

for root, _, files in os.walk(directory):
    for f in files:
        if f.endswith('.blade.php'):
            filepath = os.path.join(root, f)
            try:
                with open(filepath, 'r', encoding='utf-8') as file:
                    content = file.read()
            except UnicodeDecodeError:
                with open(filepath, 'r', encoding='windows-1256') as file:
                    content = file.read()
            
            if 'const renderItem=a=>' in content:
                new_content = pattern.sub(replacement, content)
                if new_content != content:
                    print(f'Replaced in {filepath}')
                    try:
                        with open(filepath, 'w', encoding='utf-8') as file:
                            file.write(new_content)
                    except:
                        with open(filepath, 'w', encoding='windows-1256') as file:
                            file.write(new_content)
