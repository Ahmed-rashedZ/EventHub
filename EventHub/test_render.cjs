const fs = require('fs');

const data = JSON.parse(fs.readFileSync('test_json.json', 'utf8'));
console.log(`Loaded ${data.length} events.`);

let errorCount = 0;
data.forEach((ev, i) => {
    try {
        const reviewBadge = ev.review_status === 'needs_review' 
          ? `<span ...>📝 Review Required</span>`
          : ev.review_status === 'reviewed'
          ? `<span ...>🔄 Updated</span>`
          : '';

        const reviewRow = ev.review_status === 'needs_review' && ev.review_message 
          ? `<tr>...</tr>` : '';

        // Emulate badge/timeBadge call
        const badge = (status) => `<span class="badge badge-${status}">status</span>`;
        const timeBadge = (status) => status ? `<span class="badge badge-${status}">status</span>` : '';
        const fmtDateShort = (dt) => dt ? new Date(dt).toLocaleDateString() : '—';

        // Wait! The logic itself doesn't crash unless there's a missing property throwing TypeError.
        // What about `ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' (External)' : '—')` ?
        const venueName = ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' (External)' : '—');
        
        // Wait, if ev.review_fields is not an array, `(ev.review_fields || []).map(...)` will throw error!
        if (ev.review_fields !== null && !Array.isArray(ev.review_fields)) {
            console.log(`Event ${ev.id}: review_fields is not an array, it is ${typeof ev.review_fields}`);
        }
        
    } catch(err) {
        console.error(`Error processing event ${ev.id}: ${err.message}`);
        errorCount++;
    }
});

console.log(`Finished processing with ${errorCount} errors.`);
