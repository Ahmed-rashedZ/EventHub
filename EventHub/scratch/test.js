const a = {
  start_time: '10:00',
  end_time: '11:00',
  title: 'Test Activity',
  description: 'Test Description'
};

try {
  const renderItem = a => `<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;"><div style="display:flex;align-items:center;gap:10px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>${a.description ? `<div style="font-size:0.75rem;color:#94a3b8;margin-inline-start:126px;">${a.description}</div>` : ''}</div>`;
  console.log('Parsed successfully! Render output:');
  console.log(renderItem(a));
} catch(err) {
  console.error('Error parsing/running:', err);
}
