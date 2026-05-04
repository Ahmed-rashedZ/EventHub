
    let allEvents = [];
    let globalVenues = [];
    window.currentVenueBookings = [];
    let fpInstance = null;

    const user = requireRole('Event Manager');
    if (user) { 
        populateSidebar(user); 
        setActiveNav(); 
        loadEvents(); 
        loadVenues(); 

        fpInstance = initFlatpickr("#e-booking-date", {
            showStats: true,
            disable: [
                function(date) {
                    if (!window.currentVenueBookings || !window.currentVenueBookings.length) return false;
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    const dateStrLocal = `${y}-${m}-${d}`;
                    
                    const bookings = window.currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
                    if (bookings.length > 0) {
                        // Maintenance dates are always fully blocked
                        const hasMaint = bookings.some(b => b.type === 'maintenance');
                        if (hasMaint) return true;
                        
                        const periods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
                        return periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'));
                    }
                    return false;
                }
            ],
            onChange: function() {
                checkAvailability();
            },
            onOpenBefore: function(selectedDates, dateStr, fp) {
                const venueSelect = document.getElementById('e-venue');
                const locationType = document.getElementById('e-location-type').value;
                if (locationType === 'internal' && !venueSelect.value) {
                    setTimeout(() => fp.close(), 0);
                    showToast(document.documentElement.lang === 'ar' ? 'يرجى اختيار القاعة أولاً.' : 'Please select a venue first.', 'warning');
                }
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                dayElem.classList.remove('date-fully-booked', 'date-partially-booked', 'date-maintenance');
                // Clean up any previous tooltip
                const oldTip = dayElem.querySelector('.maint-tooltip');
                if (oldTip) oldTip.remove();
                dayElem.removeAttribute('data-maint-reason');

                if (!window.currentVenueBookings || !window.currentVenueBookings.length) return;
                
                const y = dayElem.dateObj.getFullYear();
                const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
                const dateStrLocal = `${y}-${m}-${d}`;
                
                const bookings = window.currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
                if (bookings.length > 0) {
                    // Check maintenance first
                    const maintBooking = bookings.find(b => b.type === 'maintenance');
                    if (maintBooking) {
                        dayElem.classList.add('date-maintenance');
                        const reason = maintBooking.reason || null;
                        if (reason) {
                            dayElem.setAttribute('data-maint-reason', reason);
                            // Add tooltip element
                            const tooltip = document.createElement('div');
                            tooltip.className = 'maint-tooltip';
                            tooltip.textContent = `🔧 ${reason}`;
                            dayElem.appendChild(tooltip);
                        }
                        return;
                    }
                    
                    const periods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
                    if (periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'))) {
                        dayElem.classList.add('date-fully-booked');
                    } else {
                        dayElem.classList.add('date-partially-booked');
                    }
                }
            }
        });

        document.getElementById('e-period').addEventListener('change', function(e) {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.getAttribute('data-booked') === 'true') {
                showToast(document.documentElement.lang === 'ar' ? 'هذه الفترة محجوزة مسبقاً، يرجى اختيار فترة أخرى.' : 'This period is already booked, please choose another.', 'error');
                this.value = ''; 
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('flatpickr-day') && e.target.classList.contains('flatpickr-disabled')) {
                if (e.target.classList.contains('date-maintenance')) {
                    const reason = e.target.getAttribute('data-maint-reason');
                    const isAr = document.documentElement.lang === 'ar';
                    let msg = isAr ? '🔧 هذا التاريخ محجوز للصيانة' : '🔧 This date is reserved for maintenance';
                    if (reason) {
                        msg += isAr ? ` (السبب: ${reason})` : ` (Reason: ${reason})`;
                    }
                    msg += isAr ? '، يرجى اختيار تاريخ آخر.' : ', please choose another date.';
                    showToast(msg, 'error');
                } else if (e.target.classList.contains('date-fully-booked')) {
                    showToast(document.documentElement.lang === 'ar' ? 'هذا التاريخ محجوز بالكامل، يرجى اختيار تاريخ آخر.' : 'This date is fully booked, please choose another.', 'error');
                }
            }
        }, true);
    }

    async function loadEvents() {
      const res = await api.get('/events/list/my');
      const tbody = document.getElementById('events-body');
      if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
      allEvents = res.data;
      applyFilter();
    }

    function applyFilter() {
      const f = document.getElementById('filter-status').value;
      const q = (document.getElementById('search-input').value || '').toLowerCase().trim();
      const s = document.getElementById('sort-events').value;
      const now = new Date();

      // 1. Filter by status
      let filtered = f ? allEvents.filter(e => e.status === f) : [...allEvents];

      // 2. Filter by search query
      if (q) {
        filtered = filtered.filter(e => (e.title || '').toLowerCase().includes(q));
      }

      // 3. Filter by time-status when sort is live/ended
      if (s === 'live') {
        filtered = filtered.filter(e => {
          const start = new Date(e.start_time);
          const end = new Date(e.end_time);
          return start <= now && end >= now;
        });
      } else if (s === 'ended') {
        filtered = filtered.filter(e => new Date(e.end_time) < now);
      }

      // 4. Sort
      if (s === 'soonest') {
        filtered.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
      } else if (s === 'farthest') {
        filtered.sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
      } else if (s === 'alpha') {
        filtered.sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ar'));
      } else if (s === 'live') {
        // Sort live events by the one ending soonest first
        filtered.sort((a, b) => new Date(a.end_time) - new Date(b.end_time));
      } else if (s === 'ended') {
        // Sort ended events by most recently ended first
        filtered.sort((a, b) => new Date(b.end_time) - new Date(a.end_time));
      }

      renderEvents(filtered);
    }

    function renderEvents(events) {
      const tbody = document.getElementById('events-body');
      if (!events.length) { tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📅</div><p>No events found</p></div></td></tr>'; return; }
      tbody.innerHTML = events.map((ev, i) => {
        const reviewBadge = ev.review_status === 'needs_review' 
          ? `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:8px;font-size:0.7rem;font-weight:600;border:1px solid rgba(245,158,11,0.25);margin-left:6px;">📝 Review Required</span>`
          : ev.review_status === 'reviewed'
          ? `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(59,130,246,0.12);color:#3b82f6;padding:2px 8px;border-radius:8px;font-size:0.7rem;font-weight:600;border:1px solid rgba(59,130,246,0.25);margin-left:6px;">🔄 Updated</span>`
          : '';

        const reviewRow = ev.review_status === 'needs_review' && ev.review_message 
          ? `<tr><td colspan="8" style="padding:0;border:none;">
              <div style="margin:0 16px 12px; padding:12px 16px; background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.15); border-radius:10px; display:flex; align-items:center; gap:12px;">
                <span style="font-size:1.3rem;">📝</span>
                <div style="flex:1;">
                  <div style="font-size:0.7rem;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Admin Review</div>
                  <div style="font-size:0.85rem;color:#e2e8f0;line-height:1.4;">${ev.review_message}</div>
                  <div style="font-size:0.72rem;color:#94a3b8;margin-top:4px;">Fields to update: ${(ev.review_fields || []).map(f => `<span style="background:rgba(255,255,255,0.06);padding:1px 6px;border-radius:4px;margin-right:4px;">${f}</span>`).join('')}</div>
                </div>
                <button class="btn btn-sm" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);white-space:nowrap;" onclick="openEditModal(${ev.id})">✏️ Edit</button>
              </div>
            </td></tr>` 
          : '';

        return `
        <tr>
          <td style="color:var(--text-muted)">${i + 1}</td>
          <td><div style="font-weight:600">${ev.title}</div></td>
          <td style="color:var(--text-muted)">${ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' (External)' : '—')}</td>
          <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
          <td style="color:var(--text-muted)">${ev.capacity}</td>
          <td>
             <div style="display:flex; align-items:center;">
               <input type="checkbox" id="spon-tog-${ev.id}" ${ev.is_sponsorship_open ? 'checked' : ''} onchange="toggleSponsorship(${ev.id}, this.checked)" style="width:16px; height:16px; margin-right:5px; cursor:pointer;"/>
               <label for="spon-tog-${ev.id}" style="font-size:12px; cursor:pointer;">Open</label>
             </div>
          </td>
          <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''} ${reviewBadge}</td>
          <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap">
            <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${ev.id})" title="View Details">ℹ️ Details</button>
            <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/manager/event-stats/${ev.id}'" title="View Statistics">📊 Stats</button>
            ${ev.status === 'pending' ? `<button class="btn btn-sm" style="background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.25)" onclick="deleteEvent(${ev.id})" title="Delete Event">🗑️ Delete</button>` : ''}
          </td>
        </tr>${reviewRow}`;
      }).join('');
    }

    // Modal for event details
    const typeIcons = { 'مؤتمر': '🎙️', 'ندوة': '📖', 'ورشة عمل': '🔧', 'دورة تدريبية': '🎓', 'ترفيه': '🎭', 'ملتقى علمي': '🔬', 'رياضة': '⚽', 'تقنية': '💻', 'اجتماعية': '🤝' };
    const typeColors = { 'مؤتمر': '#3b82f6', 'ندوة': '#8b5cf6', 'ورشة عمل': '#10b981', 'دورة تدريبية': '#06b6d4', 'ترفيه': '#ec4899', 'ملتقى علمي': '#f59e0b', 'رياضة': '#22c55e', 'تقنية': '#6366f1', 'اجتماعية': '#f97316' };

    function showEventDetails(eventId) {
      const modal = document.getElementById('event-details-modal');
      const content = document.getElementById('event-details-content');
      modal.classList.add('open');
      content.innerHTML = '<div class="spinner" style="margin:auto"></div>';

      Promise.all([
        api.get(`/events/${eventId}`),
        api.get(`/events/${eventId}/reviews`)
      ]).then(([res, revRes]) => {
        if (!res.ok) {
          content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch event details</p></div>';
          return;
        }
        const ev = res.data;
        const reviewData = revRes.ok ? revRes.data : { average_rating: 0, reviews: [] };
        const eType = ev.event_type || 'Other';
        const tColor = typeColors[eType] || typeColors.Other;
        const tIcon = typeIcons[eType] || '📌';

        const bannerSection = ev.image
          ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
          : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji">${tIcon}</span><div class="ed-banner-fade"></div></div>`;

        const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
          ? `<div class="ed-rejection"><span class="ed-rej-label">⚠ Rejection Reason</span><p>${ev.rejection_reason}</p></div>`
          : '';

        let sponsorsHtml = '';
        if (ev.sponsors && ev.sponsors.length > 0) {
          const getTierBadge = (tier) => {
            switch (tier) {
              case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 8px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:10px;">💎 Diamond</span>';
              case 'gold': return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 8px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:10px;">🥇 Gold</span>';
              case 'silver': return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 8px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:10px;">🥈 Silver</span>';
              case 'bronze': return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 8px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:10px;">🥉 Bronze</span>';
              default: return `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 8px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:10px;">${tier || 'Sponsor'}</span>`;
            }
          };

          sponsorsHtml = `
          <div class="ed-section mt-4" style="margin-top: 16px;">
            <div class="ed-section-label">Current Sponsors</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
              ${ev.sponsors.map(sp => `
                 <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="navigateToProfile(${sp.id})">
                    <div class="avatar" style="width:36px; height:36px; font-size:14px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
                        ${(() => {
              const src = sp.image || sp.avatar || sp.profile?.logo;
              if (src) {
                const fullSrc = (src.startsWith('http') || src.startsWith('/')) ? src : '/storage/' + src;
                return `<img src="${fullSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.parentElement.innerText='${sp.name?.charAt(0).toUpperCase() || '?'}'">`;
              }
              return sp.name ? sp.name.charAt(0).toUpperCase() : '?';
            })()}
                    </div>
                    <div style="flex:1">
                        <div style="font-size:0.85rem; font-weight:600; color:#fff;">${sp.name}</div>
                        <div style="margin-top: 2px;">${getTierBadge(sp.pivot?.tier)}</div>
                    </div>
                 </div>
              `).join('')}
            </div>
          </div>
        `;
        }

        let reviewsHtml = '';
        if (reviewData.reviews.length > 0) {
          reviewsHtml = `
          <div class="ed-section" style="margin-top: 16px;">
            <div class="ed-section-label" style="display:flex;justify-content:space-between;align-items:center;">
               <span>👥 Attendee Reviews</span>
               <span style="color:#eab308;font-weight:700;font-size:0.8rem">⭐ ${Number(reviewData.average_rating).toFixed(1)}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px; max-height:250px; overflow-y:auto; padding-right:4px;">
              ${reviewData.reviews.map(r => `
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:10px; padding:12px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                      <div class="avatar" style="width:36px; height:36px; font-size:14px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
                        ${(() => {
              const src = r.user?.image || r.user?.avatar || r.user?.profile?.logo;
              if (src) {
                const fullSrc = (src.startsWith('http') || src.startsWith('/')) ? src : '/storage/' + src;
                return `<img src="${fullSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.parentElement.innerText='${r.user?.name?.charAt(0).toUpperCase() || '?'}'">`;
              }
              return r.user?.name ? r.user.name.charAt(0).toUpperCase() : '?';
            })()}
                      </div>
                      <span style="font-size:0.8rem; font-weight:600; color:#fff">${r.user?.name || 'Anonymous'}</span>
                    </div>
                    <div style="color:#eab308; font-size:0.8rem;">${'⭐'.repeat(r.rating)}</div>
                  </div>
                  ${r.review_text ? `<p style="font-size:0.85rem; color:rgba(255,255,255,0.7); margin:0;">"${r.review_text}"</p>` : '<p style="font-size:0.85rem; color:rgba(255,255,255,0.3); margin:0; font-style:italic">No written comment</p>'}
                </div>
              `).join('')}
            </div>
          </div>
        `;
        }

        content.innerHTML = `
      ${bannerSection}
      <div class="ed-body">

        <!-- Header: Title + Type + Badges -->
        <div class="ed-header">
          <div class="ed-title-row">
            <h2 class="ed-title">${ev.title}</h2>
            <span class="ed-type-pill" style="--tcolor:${tColor}">${tIcon} ${eType}</span>
          </div>
          <div class="ed-badges">
            ${ev.status ? badge(ev.status) : ''}
            ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
          </div>
        </div>

        ${rejectionSection}

        <!-- Description -->
        <div class="ed-section">
          <div class="ed-section-label">About this Event</div>
          <p class="ed-description">${ev.description || 'No description provided.'}</p>
        </div>

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">🏛️</div>
              <div>
                <div class="ed-info-label">Venue</div>
                <div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div>
              </div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">📍</div>
              <div>
                <div class="ed-info-label">Location</div>
                <div class="ed-info-value">
                  ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
                  : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—')}
                </div>
              </div>
            </div>
          ${!ev.venue_id && ev.booking_proof_path ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1; background:rgba(34,211,238,0.05); border-color:rgba(34,211,238,0.2);">
            <div class="ed-info-icon">📎</div>
            <div><div class="ed-info-label" style="color:#22d3ee">Booking Proof</div><div class="ed-info-value"><a href="/storage/${ev.booking_proof_path}" target="_blank" style="color:#22d3ee;text-decoration:underline;">View Document ↗</a></div></div>
          </div>
          ` : ''}
          ${ev.ministry_document_path ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(139,92,246,0.05); border-color:rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.2);">
            <div class="ed-info-icon">📄</div>
            <div><div class="ed-info-label" style="color:#a78bfa">Ministry Approval Document</div><div class="ed-info-value"><a href="/storage/${ev.ministry_document_path}" target="_blank" style="color:#a78bfa;text-decoration:underline;">View Document ↗</a></div></div>
          </div>
          ` : `
          <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(239,68,68,0.05); border-color:rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.2);">
            <div class="ed-info-icon">⚠️</div>
            <div><div class="ed-info-label" style="color:#ef4444">Ministry Document</div><div class="ed-info-value" style="color:#ef4444;">Not uploaded</div></div>
          </div>
          `}

          ${(() => {
            const schedule = ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule : 
                             (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null);
            if (schedule) {
              return `
                <div style="grid-column: 1 / -1;">
                  <div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">📅 Event Schedule (${schedule.length} day${schedule.length > 1 ? 's' : ''})</div>
                  <div style="display:flex;flex-direction:column;gap:6px;">
                    ${schedule.map(slot => {
                      const d = new Date(slot.date + 'T00:00:00');
                      const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                      const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                      return `<div style="display:flex;align-items:center;gap:10px;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.15);border-radius:10px;padding:10px 14px;">
                        <div style="min-width:42px;text-align:center;background:rgba(139,92,246,0.12);border-radius:8px;padding:5px 4px;">
                          <div style="font-size:0.55rem;font-weight:700;color:#a78bfa;text-transform:uppercase;">${dayNames[d.getDay()]}</div>
                          <div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">${d.getDate()}</div>
                          <div style="font-size:0.5rem;color:#94a3b8;">${monthNames[d.getMonth()]}</div>
                        </div>
                        <div style="flex:1;display:flex;align-items:center;gap:8px;">
                          ${slot.period ? `<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;text-transform:capitalize;">${slot.period.replace('_', ' ')}</span>` : ''}
                          <span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">${slot.start_time}</span>
                          <span style="color:#64748b;font-size:0.8rem;">→</span>
                          <span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">${slot.end_time}</span>
                        </div>
                      </div>`;
                    }).join('')}
                  </div>
                </div>
              `;
            } else {
              return `
                <div class="ed-info-card ed-info-accent">
                  <div class="ed-info-icon">🕐</div>
                  <div>
                    <div class="ed-info-label">Start</div>
                    <div class="ed-info-value">${fmtDate(ev.start_time)}</div>
                  </div>
                </div>
                <div class="ed-info-card ed-info-accent">
                  <div class="ed-info-icon">🕔</div>
                  <div>
                    <div class="ed-info-label">End</div>
                    <div class="ed-info-value">${fmtDate(ev.end_time)}</div>
                  </div>
                </div>
              `;
            }
          })()}
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">👥</div>
            <div>
              <div class="ed-info-label">Capacity</div>
              <div class="ed-info-value">${ev.capacity}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">🎟️</div>
            <div>
              <div class="ed-info-label">Tickets Booked</div>
              <div class="ed-info-value">${ev.tickets_count ?? '—'}</div>
            </div>
          </div>
          </div>
          
          ${sponsorsHtml}
          ${reviewsHtml}

          <!-- Agenda Section -->
          ${(() => {
            const agenda = ev.agenda;
            const hasAgenda = agenda && typeof agenda === 'object' && (Array.isArray(agenda) ? agenda.length > 0 : Object.keys(agenda).length > 0);
            let agendaHtml = '';
            if (hasAgenda) {
              agendaHtml += `<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">📋 Event Agenda</div>`;
              if (typeof agenda === 'object' && !Array.isArray(agenda)) {
                const dn=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                Object.keys(agenda).sort().forEach(dateStr => {
                  const items = agenda[dateStr];
                  if (!items || items.length === 0) return;
                  const d = new Date(dateStr + 'T00:00:00');
                  const dayLabel = `${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}`;
                  agendaHtml += `<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-block;">📅 ${dayLabel}</div><div style="display:flex;flex-direction:column;gap:4px;">`;
                  items.forEach(a => {
                    agendaHtml += `<div style="display:flex;align-items:center;gap:10px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin-left:8px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>`;
                  });
                  agendaHtml += `</div></div>`;
                });
              } else if (Array.isArray(agenda)) {
                agendaHtml += `<div style="display:flex;flex-direction:column;gap:4px;">`;
                agenda.forEach(a => {
                  agendaHtml += `<div style="display:flex;align-items:center;gap:10px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>`;
                });
                agendaHtml += `</div>`;
              }
              agendaHtml += `</div>`;
            }
            return agendaHtml;
          })()}

          <!-- Agenda Management Button (Manager only) -->
          <div style="margin-top:12px;display:flex;justify-content:center;">
            <button class="btn btn-sm" style="background:rgba(34,211,238,0.1);color:#22d3ee;border:1px solid rgba(34,211,238,0.2);display:flex;align-items:center;gap:6px;" onclick="openAgendaEditor(${ev.id})">
              📋 ${(ev.agenda && typeof ev.agenda === 'object' && (Array.isArray(ev.agenda) ? ev.agenda.length > 0 : Object.keys(ev.agenda).length > 0)) ? 'Edit' : 'Add'} Agenda
            </button>
          </div>

          <!-- Footer -->
          <div class="ed-footer" style="margin-top: 8px;">
          <span class="ed-footer-label">Created by</span>
          <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
        </div>

      </div>
    `;
      });
    }

    function closeEventDetailsModal() {
      document.getElementById('event-details-modal').classList.remove('open');
      document.getElementById('event-details-content').innerHTML = '';
    }

    // ── Agenda Editor ─────────────────────────────
    let agendaEditingEventId = null;
    let agendaEventData = null;
    let agendaDays = [];
    let agendaSelectedDay = null;
    let agendaPerDay = {}; // { "2026-05-04": [{title, start_time, end_time}], ... }

    async function openAgendaEditor(eventId) {
      agendaEditingEventId = eventId;
      const container = document.getElementById('agenda-items-editor');
      container.innerHTML = '';

      // Fetch current event data
      const res = await api.get(`/events/${eventId}`);
      if (!res.ok) { showToast('Error loading event', 'error'); return; }
      
      agendaEventData = res.data;
      
      // Determine days
      agendaDays = [];
      if (agendaEventData.external_schedule && agendaEventData.external_schedule.length > 0) {
        agendaDays = agendaEventData.external_schedule.map(s => s.date).sort();
      } else if (agendaEventData.internal_schedule && agendaEventData.internal_schedule.length > 0) {
        agendaDays = agendaEventData.internal_schedule.map(s => s.date).sort();
      } else if (agendaEventData.booking_date) {
        agendaDays = [agendaEventData.booking_date.split('T')[0]];
      } else if (agendaEventData.start_time) {
        agendaDays = [agendaEventData.start_time.split('T')[0]];
      }

      // Load existing agenda
      agendaPerDay = {};
      const existing = agendaEventData.agenda;
      if (existing && typeof existing === 'object' && !Array.isArray(existing)) {
        // New per-day format
        agendaPerDay = JSON.parse(JSON.stringify(existing));
      } else if (Array.isArray(existing) && existing.length > 0) {
        // Legacy flat format — assign to first day
        if (agendaDays.length > 0) {
          agendaPerDay[agendaDays[0]] = JSON.parse(JSON.stringify(existing));
        }
      }

      // Ensure all days have an entry
      agendaDays.forEach(d => { if (!agendaPerDay[d]) agendaPerDay[d] = []; });

      renderAgendaDayTabs();
      if (agendaDays.length > 0) selectAgendaDay(agendaDays[0]);

      // Show "Apply to All" only if multi-day
      document.getElementById('agenda-apply-all-wrap').style.display = agendaDays.length > 1 ? 'block' : 'none';

      document.getElementById('agenda-editor-modal').classList.add('open');
    }

    function renderAgendaDayTabs() {
      const tabsEl = document.getElementById('agenda-day-tabs');
      if (agendaDays.length <= 1) { tabsEl.innerHTML = ''; return; }

      const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
      const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

      tabsEl.innerHTML = agendaDays.map(dateStr => {
        const d = new Date(dateStr + 'T00:00:00');
        const dayName = dayNames[d.getDay()];
        const dayNum = d.getDate();
        const month = monthNames[d.getMonth()];
        const isActive = dateStr === agendaSelectedDay;
        const itemCount = (agendaPerDay[dateStr] || []).length;
        return `<button type="button" onclick="selectAgendaDay('${dateStr}')" style="
          padding:8px 14px;border-radius:10px;cursor:pointer;transition:all 0.2s;border:1px solid ${isActive ? 'rgba(139,92,246,0.5)' : 'rgba(255,255,255,0.08)'};
          background:${isActive ? 'rgba(139,92,246,0.15)' : 'rgba(255,255,255,0.03)'};color:${isActive ? '#c4b5fd' : '#94a3b8'};
          display:flex;flex-direction:column;align-items:center;min-width:60px;font-family:inherit;
        ">
          <span style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">${dayName}</span>
          <span style="font-size:1.15rem;font-weight:800;color:${isActive ? '#fff' : '#cbd5e1'};line-height:1.2;">${dayNum}</span>
          <span style="font-size:0.55rem;color:#64748b;">${month}</span>
          ${itemCount > 0 ? `<span style="margin-top:3px;font-size:0.55rem;background:rgba(34,211,238,0.15);color:#22d3ee;padding:1px 6px;border-radius:4px;">${itemCount} items</span>` : ''}
        </button>`;
      }).join('');
    }

    function selectAgendaDay(dateStr) {
      // Save current day's items before switching
      if (agendaSelectedDay) {
        agendaPerDay[agendaSelectedDay] = collectAgendaItems('agenda-items-editor');
      }

      agendaSelectedDay = dateStr;
      renderAgendaDayTabs(); // re-render tabs for active state

      // Load items for selected day
      const container = document.getElementById('agenda-items-editor');
      container.innerHTML = '';
      const items = agendaPerDay[dateStr] || [];
      items.forEach(item => addAgendaItem('agenda-items-editor', item));
    }

    function applyAgendaToAllDays() {
      if (!agendaSelectedDay) return;
      // Save current
      agendaPerDay[agendaSelectedDay] = collectAgendaItems('agenda-items-editor');
      const currentItems = agendaPerDay[agendaSelectedDay];
      if (!currentItems || currentItems.length === 0) {
        showToast('Add agenda items to the current day first, then apply to all.', 'error');
        return;
      }
      agendaDays.forEach(d => {
        agendaPerDay[d] = JSON.parse(JSON.stringify(currentItems));
      });
      renderAgendaDayTabs();
      showToast(`Agenda applied to all ${agendaDays.length} days!`, 'success');
    }

    function closeAgendaEditor() {
      document.getElementById('agenda-editor-modal').classList.remove('open');
      document.getElementById('agenda-items-editor').innerHTML = '';
      agendaEditingEventId = null;
      agendaEventData = null;
      agendaDays = [];
      agendaSelectedDay = null;
      agendaPerDay = {};
    }

    async function saveAgenda() {
      if (!agendaEditingEventId) return;
      
      // Save current day's items
      if (agendaSelectedDay) {
        agendaPerDay[agendaSelectedDay] = collectAgendaItems('agenda-items-editor');
      }

      // Clean empty days
      const cleanAgenda = {};
      Object.keys(agendaPerDay).forEach(day => {
        if (agendaPerDay[day] && agendaPerDay[day].length > 0) {
          cleanAgenda[day] = agendaPerDay[day];
        }
      });

      // Validate bounds
      let isValid = true;
      for (const day of Object.keys(cleanAgenda)) {
         let startBound = "00:00", endBound = "23:59";
         if (agendaEventData.external_schedule && agendaEventData.external_schedule.length > 0) {
            const s = agendaEventData.external_schedule.find(x => x.date === day);
            if (s) { startBound = s.start_time; endBound = s.end_time; }
         } else if (agendaEventData.start_time) {
            startBound = agendaEventData.start_time.split('T')[1].substring(0,5);
            endBound = agendaEventData.end_time.split('T')[1].substring(0,5);
         }
         
         const items = cleanAgenda[day] || [];
         for (const item of items) {
            if (item.start_time < startBound || item.end_time > endBound) {
               showToast(`Invalid time in ${day}. Agenda must be between ${startBound} and ${endBound}`, 'error');
               isValid = false;
               break;
            }
         }
         if (!isValid) break;
      }
      if (!isValid) return;

      const agenda = Object.keys(cleanAgenda).length > 0 ? cleanAgenda : null;
      const res = await api.put(`/events/${agendaEditingEventId}/agenda`, { agenda });
      if (res.ok) {
        showToast('Agenda saved successfully!', 'success');
        const savedEventId = agendaEditingEventId;
        closeAgendaEditor();
        loadEvents(); // refresh list
        showEventDetails(savedEventId);
      } else {
        showToast(res.data?.message || 'Error saving agenda', 'error');
      }
    }

    function collectAgendaItems(containerId) {
      const items = document.querySelectorAll(`#${containerId} .agenda-item`);
      const agenda = [];
      items.forEach(item => {
        const title = item.querySelector('.agenda-title').value.trim();
        const startTime = item.querySelector('.agenda-start').value;
        const endTime = item.querySelector('.agenda-end').value;
        if (title && startTime && endTime) {
          agenda.push({ title, start_time: startTime, end_time: endTime });
        }
      });
      return agenda;
    }



    async function toggleSponsorship(eventId, checked) {
      const res = await api.patch(`/events/${eventId}/toggle-sponsorship`);
      if (res.ok) {
        showToast(res.data.is_sponsorship_open ? 'Sponsorship is now OPEN for this event.' : 'Sponsorship is now CLOSED for this event.', 'success');
      } else {
        showToast(res.data?.message || 'Error updating status', 'error');
        document.getElementById(`spon-tog-${eventId}`).checked = !checked; // revert UI visually
      }
    }

    async function deleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) return;
      const res = await api.delete(`/events/${eventId}`);
      if (res.ok) {
        showToast('Event deleted successfully', 'success');
        loadEvents();
      } else {
        showToast(res.data?.message || 'Error deleting event', 'error');
      }
    }

    // ── Internal Venue Multi-Day Calendar ─────────────────────────
    let intCalendarInstance = null;
    let intSelectedDates = [];

    function initIntCalendar() {
      if (intCalendarInstance) return;

      const wrap = document.getElementById('e-int-calendar-wrap');
      wrap.innerHTML = ''; // clear

      intCalendarInstance = flatpickr(wrap, {
        mode: 'multiple',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        inline: true,
        animate: true,
        appendTo: wrap,
        disable: [
            function(date) {
                if (!window.currentVenueBookings || !window.currentVenueBookings.length) return false;
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                const dateStrLocal = `${y}-${m}-${d}`;
                
                const bookings = window.currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
                if (bookings.length > 0) {
                    const hasMaint = bookings.some(b => b.type === 'maintenance');
                    if (hasMaint) return true;
                    
                    const periods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
                    return periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'));
                }
                return false;
            }
        ],
        onChange: function(selectedDates, dateStr) {
          if (selectedDates.length >= 2) {
            const sorted = [...selectedDates].sort((a, b) => a - b);
            const first = sorted[0];
            const last = sorted[sorted.length - 1];
            const diffDays = Math.ceil((last - first) / (1000 * 60 * 60 * 24));
            if (diffDays > 14) {
              showToast('The span between first and last day cannot exceed 14 days.', 'error');
              const prev = selectedDates.slice(0, -1);
              intCalendarInstance.setDate(prev, false);
              return;
            }
          }

          intSelectedDates = selectedDates.map(d => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
          }).sort();
          renderIntTimeSlots();
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            dayElem.classList.remove('date-fully-booked', 'date-partially-booked', 'date-maintenance');
            const oldTip = dayElem.querySelector('.maint-tooltip');
            if (oldTip) oldTip.remove();
            dayElem.removeAttribute('data-maint-reason');

            if (!window.currentVenueBookings || !window.currentVenueBookings.length) return;
            
            const y = dayElem.dateObj.getFullYear();
            const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
            const dateStrLocal = `${y}-${m}-${d}`;
            
            const bookings = window.currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
            if (bookings.length > 0) {
                const maintBooking = bookings.find(b => b.type === 'maintenance');
                if (maintBooking) {
                    dayElem.classList.add('date-maintenance');
                    if (maintBooking.reason) {
                        dayElem.setAttribute('data-maint-reason', maintBooking.reason);
                        const tooltip = document.createElement('div');
                        tooltip.className = 'maint-tooltip';
                        tooltip.textContent = `🔧 ${maintBooking.reason}`;
                        dayElem.appendChild(tooltip);
                    }
                    return;
                }
                
                const periods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
                if (periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'))) {
                    dayElem.classList.add('date-fully-booked');
                } else {
                    dayElem.classList.add('date-partially-booked');
                }
            }
        }
      });
    }

    function renderIntTimeSlots() {
      const container = document.getElementById('int-calendar-slots');

      if (intSelectedDates.length === 0) {
        container.innerHTML = '';
        return;
      }

      const existingPeriods = {};
      container.querySelectorAll('.int-slot-card').forEach(card => {
        const date = card.dataset.date;
        const periodEl = card.querySelector('.int-slot-period');
        if (periodEl) existingPeriods[date] = periodEl.value;
      });

      const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

      container.innerHTML = intSelectedDates.map((dateStr) => {
        const d = new Date(dateStr + 'T00:00:00');
        const dayName = dayNames[d.getDay()];
        const monthName = monthNames[d.getMonth()];
        const dayNum = d.getDate();
        const prevPeriod = existingPeriods[dateStr] || '';
        
        const bookings = window.currentVenueBookings ? window.currentVenueBookings.filter(b => b.booking_date === dateStr) : [];
        const bookedPeriods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);

        return `
        <div class="int-slot-card" data-date="${dateStr}" style="
          background: rgba(255,255,255,0.03); border: 1px solid rgba(139,92,246,0.2);
          border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 14px;
        ">
          <div style="min-width: 52px; text-align: center; background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(124,58,237,0.1)); border: 1px solid rgba(139,92,246,0.25); border-radius: 10px; padding: 8px 6px;">
            <div style="font-size:0.65rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.05em;">${dayName}</div>
            <div style="font-size:1.3rem;font-weight:800;color:#fff;line-height:1;">${dayNum}</div>
            <div style="font-size:0.6rem;color:#94a3b8;margin-top:1px;">${monthName}</div>
          </div>
          <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;">Select Period</label>
            <select class="int-slot-period form-control" style="padding:6px 10px;font-size:0.85rem;" onchange="checkIntPeriodAvailability(this, '${dateStr}')" required>
              <option value="">Select a period...</option>
              <option value="morning" ${prevPeriod === 'morning' ? 'selected' : ''} ${bookedPeriods.includes('morning') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>Morning Period ☀</option>
              <option value="evening" ${prevPeriod === 'evening' ? 'selected' : ''} ${bookedPeriods.includes('evening') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>Evening Period 🌙</option>
              <option value="full_day" ${prevPeriod === 'full_day' ? 'selected' : ''} ${bookedPeriods.includes('morning') || bookedPeriods.includes('evening') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>Full Day 🗓️</option>
            </select>
          </div>
          <button type="button" onclick="removeIntDay('${dateStr}')" style="
            background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
            color:#ef4444; width:30px; height:30px; border-radius:8px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; font-size:0.9rem; margin-top:20px;
          " title="Remove this day">✕</button>
        </div>`;
      }).join('');
    }

    window.removeIntDay = function(dateStr) {
      if (intCalendarInstance) {
        const current = intCalendarInstance.selectedDates;
        const dObj = new Date(dateStr + 'T00:00:00');
        const newDates = current.filter(d => d.getTime() !== dObj.getTime());
        intCalendarInstance.setDate(newDates, true);
        intSelectedDates = intSelectedDates.filter(d => d !== dateStr);
        renderIntTimeSlots();
      }
    };

    window.checkIntPeriodAvailability = function(selectElem, dateStr) {
        const bookings = window.currentVenueBookings ? window.currentVenueBookings.filter(b => b.booking_date === dateStr) : [];
        const bookedPeriods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
        if (bookedPeriods.includes(selectElem.value) || (selectElem.value === 'full_day' && (bookedPeriods.includes('morning') || bookedPeriods.includes('evening'))) || (bookedPeriods.includes('full_day'))) {
            showToast('This period is already booked. Please select another.', 'error');
            selectElem.value = '';
        }
    };

    function buildInternalSchedule() {
      const cards = document.querySelectorAll('.int-slot-card');
      const schedule = [];
      cards.forEach(card => {
        const date = card.dataset.date;
        const period = card.querySelector('.int-slot-period').value;
        if (date && period) {
          schedule.push({ date, period });
        }
      });
      return schedule;
    }

    // ── External Venue Multi-Day Calendar ─────────────────────────
    let extCalendarInstance = null;
    let extSelectedDates = [];

    function initExtCalendar() {
      if (extCalendarInstance) return;

      const wrap = document.getElementById('e-ext-calendar-wrap');
      wrap.innerHTML = ''; // clear

      extCalendarInstance = flatpickr(wrap, {
        mode: 'multiple',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        inline: true,
        animate: true,
        appendTo: wrap,
        onChange: function(selectedDates, dateStr) {
          // Enforce 14-day max window
          if (selectedDates.length >= 2) {
            const sorted = [...selectedDates].sort((a, b) => a - b);
            const first = sorted[0];
            const last = sorted[sorted.length - 1];
            const diffDays = Math.ceil((last - first) / (1000 * 60 * 60 * 24));
            if (diffDays > 14) {
              showToast('The span between first and last day cannot exceed 14 days.', 'error');
              // Remove the last added date
              const prev = selectedDates.slice(0, -1);
              extCalendarInstance.setDate(prev, false);
              return;
            }
          }

          extSelectedDates = selectedDates.map(d => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
          }).sort();
          renderExtTimeSlots();
        }
      });
    }

    function renderExtTimeSlots() {
      const container = document.getElementById('ext-slots-list');
      const wrapper = document.getElementById('ext-schedule-slots');

      if (extSelectedDates.length === 0) {
        wrapper.style.display = 'none';
        container.innerHTML = '';
        return;
      }

      wrapper.style.display = 'block';

      // Preserve existing times
      const existingTimes = {};
      container.querySelectorAll('.ext-slot-card').forEach(card => {
        const date = card.dataset.date;
        const startEl = card.querySelector('.ext-slot-start');
        const endEl = card.querySelector('.ext-slot-end');
        if (startEl && endEl) {
          existingTimes[date] = { start: startEl.value, end: endEl.value };
        }
      });

      const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

      container.innerHTML = extSelectedDates.map((dateStr, i) => {
        const d = new Date(dateStr + 'T00:00:00');
        const dayName = dayNames[d.getDay()];
        const monthName = monthNames[d.getMonth()];
        const dayNum = d.getDate();
        const prev = existingTimes[dateStr] || { start: '09:00', end: '17:00' };

        return `
        <div class="ext-slot-card" data-date="${dateStr}" style="
          background: rgba(255,255,255,0.03);
          border: 1px solid rgba(139,92,246,0.2);
          border-radius: 14px;
          padding: 14px 16px;
          display: flex;
          align-items: center;
          gap: 14px;
          transition: border-color 0.2s, background 0.2s;
        ">
          <div style="
            min-width: 52px; text-align: center;
            background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(124,58,237,0.1));
            border: 1px solid rgba(139,92,246,0.25);
            border-radius: 10px; padding: 8px 6px;
          ">
            <div style="font-size:0.65rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.05em;">${dayName}</div>
            <div style="font-size:1.3rem;font-weight:800;color:#fff;line-height:1;">${dayNum}</div>
            <div style="font-size:0.6rem;color:#94a3b8;margin-top:1px;">${monthName}</div>
          </div>
          <div style="flex:1;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <div style="flex:1;min-width:100px;">
              <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:3px;">Start</label>
              <input type="time" class="ext-slot-start form-control" value="${prev.start}" style="padding:6px 10px;font-size:0.85rem;" />
            </div>
            <div style="color:#64748b;font-size:1.1rem;margin-top:14px;">→</div>
            <div style="flex:1;min-width:100px;">
              <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:3px;">End</label>
              <input type="time" class="ext-slot-end form-control" value="${prev.end}" style="padding:6px 10px;font-size:0.85rem;" />
            </div>
          </div>
          <button type="button" onclick="removeExtDay('${dateStr}')" style="
            background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
            color:#ef4444; width:30px; height:30px; border-radius:8px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; font-size:0.9rem;
            transition: background 0.2s; flex-shrink:0; margin-top:10px;
          " title="Remove this day">✕</button>
        </div>`;
      }).join('');
    }

    function removeExtDay(dateStr) {
      if (!extCalendarInstance) return;
      const current = extCalendarInstance.selectedDates.filter(d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}` !== dateStr;
      });
      extCalendarInstance.setDate(current, true);
      extSelectedDates = extSelectedDates.filter(d => d !== dateStr);
      renderExtTimeSlots();
    }

    function buildExternalSchedule() {
      const cards = document.querySelectorAll('.ext-slot-card');
      const schedule = [];
      cards.forEach(card => {
        const date = card.dataset.date;
        const startTime = card.querySelector('.ext-slot-start').value;
        const endTime = card.querySelector('.ext-slot-end').value;
        if (date && startTime && endTime) {
          schedule.push({ date, start_time: startTime, end_time: endTime });
        }
      });
      return schedule;
    }

    // ── Agenda Builder ─────────────────────────────
    function addAgendaItem(containerId, data = null) {
      const container = document.getElementById(containerId);
      const item = document.createElement('div');
      item.className = 'agenda-item';
      item.style.cssText = 'display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(139,92,246,0.15);border-radius:12px;padding:12px 14px;';
      item.innerHTML = `
        <div style="flex:1;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <div style="min-width:80px;">
            <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">From</label>
            <input type="time" class="agenda-start form-control" value="${data?.start_time || ''}" required style="padding:5px 8px;font-size:0.82rem;" />
          </div>
          <span style="color:#64748b;margin-top:12px;">→</span>
          <div style="min-width:80px;">
            <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">To</label>
            <input type="time" class="agenda-end form-control" value="${data?.end_time || ''}" required style="padding:5px 8px;font-size:0.82rem;" />
          </div>
          <div style="flex:1;min-width:120px;">
            <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Activity</label>
            <input type="text" class="agenda-title form-control" value="${data?.title || ''}" placeholder="e.g. Opening Ceremony" style="padding:5px 8px;font-size:0.82rem;" />
          </div>
        </div>
        <button type="button" onclick="this.closest('.agenda-item').remove()" style="
          background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);
          color:#ef4444;width:28px;height:28px;border-radius:7px;cursor:pointer;
          display:flex;align-items:center;justify-content:center;font-size:0.85rem;
          flex-shrink:0;margin-top:10px;
        " title="Remove">✕</button>
      `;
      container.appendChild(item);
    }



    async function loadVenues() {
      const res = await api.get('/venues');
      const sel = document.getElementById('e-venue');
      if (!res.ok) { sel.innerHTML = '<option value="">No venues available</option>'; return; }
      globalVenues = res.data;
      sel.innerHTML = '<option value="">Select a hall inside the exhibition...</option>' + res.data.map(v => `<option value="${v.id}">${v.name} (${v.location})</option>`).join('');
    }

    async function updatePeriodTimes() {
      const venueId = document.getElementById('e-venue').value;
      const periodSelect = document.getElementById('e-period').value;
      const timeLabel = document.getElementById('selected-period-time');
      
      if (!venueId) {
        timeLabel.textContent = document.documentElement.lang === 'ar' ? 'اختر قاعة أولاً لرؤية الوقت' : 'Select a venue first to see the time';
        window.currentVenueBookings = [];
        window.lastFetchedVenueId = null;
        if (fpInstance) fpInstance.redraw();
        checkAvailability();
        return;
      }
      
      const v = globalVenues.find(x => x.id == venueId);
      if (v) {
        const formatTime = (t24) => {
          if(!t24) return '';
          let [h, m] = t24.split(':');
          h = parseInt(h);
          const ampm = h >= 12 ? 'PM' : 'AM';
          h = h % 12;
          h = h ? h : 12; 
          return `${h.toString().padStart(2, '0')}:${m} ${ampm}`;
        };

        if (periodSelect === 'morning') {
          timeLabel.textContent = `Time: ${formatTime(v.morning_start)} - ${formatTime(v.morning_end)}`;
        } else if (periodSelect === 'evening') {
          timeLabel.textContent = `Time: ${formatTime(v.evening_start)} - ${formatTime(v.evening_end)}`;
        } else {
          timeLabel.textContent = `Time: ${formatTime(v.morning_start)} - ${formatTime(v.evening_end)}`;
        }
      }

      if (window.lastFetchedVenueId !== venueId) {
         window.lastFetchedVenueId = venueId;
         const res = await api.get(`/venues/${venueId}/bookings`);
         if (res.ok) {
            window.currentVenueBookings = res.data;
         } else {
            window.currentVenueBookings = [];
         }
         if (fpInstance) {
             fpInstance.redraw();
             if (fpInstance.updateCustomStats) fpInstance.updateCustomStats();
         }
      }
      checkAvailability();
    }

    function checkAvailability() {
      const date = document.getElementById('e-booking-date').value;
      const periodOpts = document.getElementById('e-period').options;
      
      if (!date) return;

      for(let i=0; i<periodOpts.length; i++) {
         periodOpts[i].removeAttribute('data-booked');
         periodOpts[i].text = periodOpts[i].text.replace(' (محجوز)', '').replace(' (Booked)', '');
      }

      const bookedPeriods = (window.currentVenueBookings || []).filter(b => b.booking_date === date).map(b => b.period);
      
      for(let i=0; i<periodOpts.length; i++) {
         const p = periodOpts[i].value;
         if (bookedPeriods.includes(p) || (bookedPeriods.includes('full_day')) || (p === 'full_day' && bookedPeriods.length > 0)) {
             periodOpts[i].setAttribute('data-booked', 'true');
             periodOpts[i].text += ` (${document.documentElement.lang === 'ar' ? 'محجوز' : 'Booked'})`;
         }
      }
      
      const currentSel = document.getElementById('e-period');
      if (currentSel.options[currentSel.selectedIndex]?.getAttribute('data-booked') === 'true') {
         currentSel.value = '';
      }
    }

    let currentWizardStep = 1;

    function openModal() { 
      currentWizardStep = 1;
      updateWizardUI();
      document.getElementById('event-modal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('event-modal').classList.remove('open');
      document.getElementById('event-form').reset();
      setLocationMode('internal');
      currentWizardStep = 1;
      updateWizardUI();
      // Reset banner preview
      const preview = document.getElementById('banner-preview');
      if (preview) preview.style.display = 'none';
      // Reset external calendar
      if (extCalendarInstance) {
        extCalendarInstance.destroy();
        extCalendarInstance = null;
      }
      extSelectedDates = [];
      const slotsContainer = document.getElementById('ext-slots-list');
      if (slotsContainer) slotsContainer.innerHTML = '';
      const slotsWrapper = document.getElementById('ext-schedule-slots');
      if (slotsWrapper) slotsWrapper.style.display = 'none';
      // Reset agenda
      const agendaCreate = document.getElementById('agenda-items-create');
      if (agendaCreate) agendaCreate.innerHTML = '';
    }

    function nextStep() {
      // Validate step 1 fields
      const title = document.getElementById('e-title').value.trim();
      const desc = document.getElementById('e-desc').value.trim();
      if (!title) { showToast('Please enter an event title', 'error'); document.getElementById('e-title').focus(); return; }
      if (!desc) { showToast('Please enter a description', 'error'); document.getElementById('e-desc').focus(); return; }
      
      currentWizardStep = 2;
      updateWizardUI();
      // Scroll modal to top
      document.querySelector('#event-modal .modal').scrollTop = 0;
    }

    function prevStep() {
      currentWizardStep = 1;
      updateWizardUI();
      document.querySelector('#event-modal .modal').scrollTop = 0;
    }

    function goToStep(step) {
      if (step === 2 && currentWizardStep === 1) {
        nextStep(); // Validate first
        return;
      }
      currentWizardStep = step;
      updateWizardUI();
      document.querySelector('#event-modal .modal').scrollTop = 0;
    }

    function updateWizardUI() {
      const step1 = document.getElementById('wizard-step-1');
      const step2 = document.getElementById('wizard-step-2');
      const num1 = document.getElementById('wiz-num-1');
      const num2 = document.getElementById('wiz-num-2');
      const label1 = document.getElementById('wiz-label-1');
      const label2 = document.getElementById('wiz-label-2');
      const line = document.getElementById('wiz-line');

      if (currentWizardStep === 1) {
        step1.style.display = 'block';
        step1.style.animation = 'wizSlideIn 0.3s ease';
        step2.style.display = 'none';
        num1.style.background = 'linear-gradient(135deg, #8b5cf6, #7c3aed)';
        num1.style.color = '#fff';
        num1.style.boxShadow = '0 3px 10px rgba(139,92,246,0.3)';
        num2.style.background = 'rgba(255,255,255,0.06)';
        num2.style.color = '#64748b';
        num2.style.boxShadow = 'none';
        label1.style.color = '#c4b5fd';
        label2.style.color = '#64748b';
        line.style.width = '0%';
      } else {
        step1.style.display = 'none';
        step2.style.display = 'block';
        step2.style.animation = 'wizSlideIn 0.3s ease';
        num1.style.background = 'linear-gradient(135deg, #10b981, #059669)';
        num1.style.color = '#fff';
        num1.style.boxShadow = '0 3px 10px rgba(16,185,129,0.3)';
        num1.innerHTML = '✓';
        num2.style.background = 'linear-gradient(135deg, #8b5cf6, #7c3aed)';
        num2.style.color = '#fff';
        num2.style.boxShadow = '0 3px 10px rgba(139,92,246,0.3)';
        label1.style.color = '#10b981';
        label2.style.color = '#c4b5fd';
        line.style.width = '100%';
      }
      // Reset num1 content when going back
      if (currentWizardStep === 1) num1.innerHTML = '1';
    }

    // Banner image preview
    document.getElementById('e-image').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('banner-preview');
      const img = document.getElementById('banner-preview-img');
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (ev) => { img.src = ev.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
      }
    });

    // ── Edit Pending Event ───────────────────────────────────────
    function openEditModal(eventId) {
      const ev = allEvents.find(e => e.id === eventId);
      if (!ev) return;

      document.getElementById('edit-event-id').value = eventId;
      document.getElementById('edit-review-msg').textContent = ev.review_message || '';

      const container = document.getElementById('edit-fields-container');
      const fields = ev.review_fields || [];
      let html = '';

      const fieldLabels = {
        title: '📝 Event Title',
        description: '📄 Description', 
        event_type: '🏷️ Event Type',
        capacity: '👥 Capacity',
        image: '🖼️ Event Banner',
        ministry_document: '📄 Ministry Document',
        booking_proof: '📎 Booking Proof'
      };

      fields.forEach(f => {
        switch(f) {
          case 'title':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-title" type="text" class="form-control" value="${ev.title || ''}" required /></div>`;
            break;
          case 'description':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <textarea id="edit-desc" class="form-control" rows="3" required>${ev.description || ''}</textarea></div>`;
            break;
          case 'event_type':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <select id="edit-type" class="form-control">
                <option value="مؤتمر" ${ev.event_type==='مؤتمر'?'selected':''}>Conference</option>
                <option value="ندوة" ${ev.event_type==='ندوة'?'selected':''}>Seminar</option>
                <option value="ورشة عمل" ${ev.event_type==='ورشة عمل'?'selected':''}>Workshop</option>
                <option value="دورة تدريبية" ${ev.event_type==='دورة تدريبية'?'selected':''}>Training</option>
                <option value="ترفيه" ${ev.event_type==='ترفيه'?'selected':''}>Entertainment</option>
                <option value="ملتقى علمي" ${ev.event_type==='ملتقى علمي'?'selected':''}>Scientific Forum</option>
                <option value="رياضة" ${ev.event_type==='رياضة'?'selected':''}>Sports</option>
                <option value="تقنية" ${ev.event_type==='تقنية'?'selected':''}>Technology</option>
                <option value="اجتماعية" ${ev.event_type==='اجتماعية'?'selected':''}>Social</option>
              </select></div>`;
            break;
          case 'capacity':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-capacity" type="number" class="form-control" value="${ev.capacity || ''}" min="1" required /></div>`;
            break;
          case 'image':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-image" type="file" accept="image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.image ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <a href="/storage/${ev.image}" target="_blank" style="color:#8b5cf6;">View ↗</a></small>` : ''}</div>`;
            break;
          case 'ministry_document':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-ministry" type="file" accept=".pdf,image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.ministry_document_path ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <a href="/storage/${ev.ministry_document_path}" target="_blank" style="color:#8b5cf6;">View ↗</a></small>` : ''}</div>`;
            break;
          case 'booking_proof':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-proof" type="file" accept=".pdf,image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.booking_proof_path ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <a href="/storage/${ev.booking_proof_path}" target="_blank" style="color:#8b5cf6;">View ↗</a></small>` : ''}</div>`;
            break;
        }
      });

      if (!html) html = '<p style="color:var(--text-muted);text-align:center;padding:20px;">No editable fields specified.</p>';
      container.innerHTML = html;
      document.getElementById('edit-modal').classList.add('open');
    }

    function closeEditModal() {
      document.getElementById('edit-modal').classList.remove('open');
      document.getElementById('edit-form').reset();
    }

    async function submitEdit(e) {
      e.preventDefault();
      const eventId = document.getElementById('edit-event-id').value;
      const formData = new FormData();

      const titleEl = document.getElementById('edit-title');
      if (titleEl) formData.append('title', titleEl.value);

      const descEl = document.getElementById('edit-desc');
      if (descEl) formData.append('description', descEl.value);

      const typeEl = document.getElementById('edit-type');
      if (typeEl) formData.append('event_type', typeEl.value);

      const capEl = document.getElementById('edit-capacity');
      if (capEl) formData.append('capacity', capEl.value);

      const imgEl = document.getElementById('edit-image');
      if (imgEl && imgEl.files[0]) formData.append('image', imgEl.files[0]);

      const minEl = document.getElementById('edit-ministry');
      if (minEl && minEl.files[0]) formData.append('ministry_document', minEl.files[0]);

      const proofEl = document.getElementById('edit-proof');
      if (proofEl && proofEl.files[0]) formData.append('booking_proof', proofEl.files[0]);

      const res = await api.postForm(`/events/${eventId}/update-pending`, formData);
      if (res.ok) {
        showToast('Event updated successfully!', 'success');
        closeEditModal();
        loadEvents();
      } else {
        const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
        showToast(msg, 'error');
      }
    }

    function setLocationMode(mode) {
      document.getElementById('e-location-type').value = mode;
      const internalWrap = document.getElementById('venue-internal-wrap');
      const externalWrap = document.getElementById('venue-external-wrap');
      const internalFields = document.getElementById('internal-fields');
      const externalFields = document.getElementById('external-fields');
      const indicator = document.getElementById('venue-toggle-indicator');
      const btnInt = document.getElementById('btn-internal');
      const btnExt = document.getElementById('btn-external');

      if (mode === 'internal') {
        indicator.style.transform = 'translateX(0)';
        btnInt.style.color = '#fff';
        btnExt.style.color = '#64748b';
        internalWrap.style.display = 'block';
        externalWrap.style.display = 'none';
        internalFields.style.display = 'block';
        externalFields.style.display = 'none';
        
        // required toggles
        document.getElementById('e-venue').required = true;
        
        document.getElementById('e-ext-name').required = false;
        document.getElementById('e-booking-proof').required = false;

        // Init internal calendar
        initIntCalendar();
      } else {
        indicator.style.transform = 'translateX(100%)';
        btnInt.style.color = '#64748b';
        btnExt.style.color = '#fff';
        internalWrap.style.display = 'none';
        externalWrap.style.display = 'block';
        internalFields.style.display = 'none';
        externalFields.style.display = 'block';
        
        // required toggles
        document.getElementById('e-venue').required = false;
        
        document.getElementById('e-ext-name').required = true;
        document.getElementById('e-booking-proof').required = true;

        // Init external calendar if not already done
        initExtCalendar();
      }
    }

    document.getElementById('event-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData();
      formData.append('title', document.getElementById('e-title').value);
      formData.append('description', document.getElementById('e-desc').value);

      const eventType = document.getElementById('e-type').value;
      formData.append('event_type', eventType);
      const locationType = document.getElementById('e-location-type').value;
      formData.append('location_type', locationType);

      if (locationType === 'internal') {
        formData.append('venue_id', document.getElementById('e-venue').value);
        
        const internalSchedule = buildInternalSchedule();
        if (!internalSchedule || internalSchedule.length === 0) {
          showToast('Please select at least one day and set a period.', 'error');
          return;
        }
        formData.append('internal_schedule', JSON.stringify(internalSchedule));
      } else {
        formData.append('external_venue_name', document.getElementById('e-ext-name').value);
        formData.append('external_venue_location', document.getElementById('e-ext-location').value);
        
        // Build external_schedule JSON from time slots
        const schedule = buildExternalSchedule();
        if (!schedule || schedule.length === 0) {
          showToast('Please select at least one day and set times.', 'error');
          return;
        }
        formData.append('external_schedule', JSON.stringify(schedule));
        
        const proofFile = document.getElementById('e-booking-proof').files[0];
        if (proofFile) {
          formData.append('booking_proof', proofFile);
        }
      }

      formData.append('capacity', document.getElementById('e-capacity').value);

      const imageFile = document.getElementById('e-image').files[0];
      if (imageFile) {
        formData.append('image', imageFile);
      }

      // Ministry document (required for all events)
      const ministryFile = document.getElementById('e-ministry-doc').files[0];
      if (ministryFile) {
        formData.append('ministry_document', ministryFile);
      }

      // Agenda (optional) - wrap in per-day format
      const agendaItems = collectAgendaItems('agenda-items-create');
      if (agendaItems && agendaItems.length > 0) {
        let isValid = true;
        
        if (locationType === 'external') {
           let startBound = '00:00', endBound = '23:59';
           const schedule = buildExternalSchedule();
           if (schedule.length > 0) {
             startBound = schedule[0].start_time;
             endBound = schedule[0].end_time;
           }
           for (const item of agendaItems) {
              if (item.start_time < startBound || item.end_time > endBound) {
                 showToast(`Agenda items must be between event start (${startBound}) and end (${endBound})`, 'error');
                 return; // Stop form submission
              }
           }
        }
        // For internal, validation per day is complex on client side as each day can have a different period.
        // We will assign it to all days and can refine later.

        const agendaObj = {};
        if (locationType === 'external') {
          const schedule = buildExternalSchedule();
          schedule.forEach(s => { agendaObj[s.date] = JSON.parse(JSON.stringify(agendaItems)); });
        } else {
          const schedule = buildInternalSchedule();
          schedule.forEach(s => { agendaObj[s.date] = JSON.parse(JSON.stringify(agendaItems)); });
        }
        formData.append('agenda', JSON.stringify(agendaObj));
      }

      const res = await api.postForm('/events', formData);

      if (res.ok) { showToast('Event submitted for approval!', 'success'); closeModal(); loadEvents(); }
      else {
        const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
        showToast(msg, 'error');
      }
    });
  