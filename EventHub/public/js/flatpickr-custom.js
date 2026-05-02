/**
 * EventHub - Custom Flatpickr Initialization
 * Premium calendar design with stats, legend, and booking indicators.
 */

window.initFlatpickr = function(selector, options = {}) {
    const isAr = document.documentElement.lang === 'ar';
    
    const defaultOptions = {
        dateFormat: "Y-m-d",
        locale: isAr ? 'ar' : 'en',
        onOpen: function(selectedDates, dateStr, fp) {
            if (options.onOpenBefore) {
                options.onOpenBefore(selectedDates, dateStr, fp);
            }
        },
        onReady: function(selectedDates, dateStr, fp) {
            // 1. Inject Premium Header
            const headerHtml = `
                <div class="fp-custom-top-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 2px; direction: ${isAr ? 'rtl' : 'ltr'};">
                    <button type="button" class="fp-close-btn" style="width: 36px; height: 36px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; order: ${isAr ? 2 : 1}; font-size: 14px;">✕</button>
                    <div style="display: flex; align-items: center; gap: 14px; order: ${isAr ? 1 : 2};">
                        <div style="text-align: ${isAr ? 'right' : 'left'};">
                            <div style="font-size: 1.1rem; font-weight: 800; color: #f1f5f9; margin-bottom: 3px; letter-spacing: -0.01em;">${isAr ? 'تقويم الحجوزات' : 'Booking Calendar'}</div>
                            <div style="font-size: 0.78rem; color: #64748b;">${isAr ? 'اضغط على أي يوم لاختياره' : 'Select an available date'}</div>
                        </div>
                        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(139, 92, 246, 0.35);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                </div>
            `;
            fp.calendarContainer.insertAdjacentHTML('afterbegin', headerHtml);

            // Close button functionality
            const closeBtn = fp.calendarContainer.querySelector('.fp-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => fp.close());
                closeBtn.addEventListener('mouseover', () => { closeBtn.style.background = 'rgba(239,68,68,0.1)'; closeBtn.style.color = '#ef4444'; closeBtn.style.borderColor = 'rgba(239,68,68,0.3)'; });
                closeBtn.addEventListener('mouseout', () => { closeBtn.style.background = 'rgba(255,255,255,0.04)'; closeBtn.style.color = '#64748b'; closeBtn.style.borderColor = 'rgba(255,255,255,0.08)'; });
            }

            // 2. Inject Stats Row
            if (options.showStats) {
                const statsHtml = `
                    <div class="fp-custom-stats" style="display: flex; flex-direction: row-reverse; gap: 10px; margin-bottom: 20px;">
                        <div style="flex: 1; background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 14px 8px; text-align: center;">
                            <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">${isAr ? 'إجمالي' : 'Total'}</div>
                            <div class="fp-stat-total" style="font-size: 1.4rem; font-weight: 800; color: #818cf8;">0</div>
                        </div>
                        <div style="flex: 1; background: rgba(239,68,68,0.04); border: 1px solid rgba(239,68,68,0.12); border-radius: 10px; padding: 14px 8px; text-align: center;">
                            <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">${isAr ? 'محجوز' : 'Booked'}</div>
                            <div class="fp-stat-booked" style="font-size: 1.4rem; font-weight: 800; color: #f87171;">0</div>
                        </div>
                        <div style="flex: 1; background: rgba(16,185,129,0.04); border: 1px solid rgba(16,185,129,0.12); border-radius: 10px; padding: 14px 8px; text-align: center;">
                            <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">${isAr ? 'متاح' : 'Available'}</div>
                            <div class="fp-stat-avail" style="font-size: 1.4rem; font-weight: 800; color: #34d399;">0</div>
                        </div>
                    </div>
                `;
                fp.calendarContainer.querySelector('.fp-custom-top-header').insertAdjacentHTML('afterend', statsHtml);
            }

            // 3. Inject Legend (Footer)
            const footerHtml = `
                <div class="fp-custom-footer" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: center; gap: 20px; font-size: 0.73rem; color: #64748b; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.08); border-radius: 3px;"></span> ${isAr ? 'متاح' : 'Available'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.5); border-radius: 3px;"></span> ${isAr ? 'محجوز' : 'Booked'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); border-radius: 3px;"></span> 🔧 ${isAr ? 'صيانة' : 'Maint.'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; border: 2px solid #8b5cf6; border-radius: 3px;"></span> ${isAr ? 'اليوم' : 'Today'}</div>
                </div>
            `;
            fp.calendarContainer.insertAdjacentHTML('beforeend', footerHtml);

            // Update stats function
            fp.updateCustomStats = function() {
                if (typeof fp.currentYear === 'undefined' || !options.showStats) return;
                
                const daysInMonth = new Date(fp.currentYear, fp.currentMonth + 1, 0).getDate();
                let bookedCount = 0;
                
                if (window.currentVenueBookings && Array.isArray(window.currentVenueBookings)) {
                    const m = String(fp.currentMonth + 1).padStart(2, '0');
                    const prefix = `${fp.currentYear}-${m}-`;
                    
                    const bookingsInMonth = window.currentVenueBookings.filter(b => {
                        const bDate = b.booking_date || b.date;
                        return bDate && String(bDate).startsWith(prefix);
                    });
                    
                    const uniqueDates = new Set();
                    bookingsInMonth.forEach(b => {
                        const bDate = b.booking_date || b.date;
                        uniqueDates.add(String(bDate));
                    });
                    bookedCount = uniqueDates.size;
                }
                
                const availCount = Math.max(0, daysInMonth - bookedCount);
                const statAvail = fp.calendarContainer.querySelector('.fp-stat-avail');
                const statBooked = fp.calendarContainer.querySelector('.fp-stat-booked');
                const statTotal = fp.calendarContainer.querySelector('.fp-stat-total');
                
                if (statAvail) statAvail.textContent = availCount;
                if (statBooked) statBooked.textContent = bookedCount;
                if (statTotal) statTotal.textContent = daysInMonth;
            };

            if (options.onReadyAfter) {
                options.onReadyAfter(selectedDates, dateStr, fp);
            }
            
            fp.updateCustomStats();
        },
        onMonthChange: function(selectedDates, dateStr, fp) {
            if (fp.updateCustomStats) fp.updateCustomStats();
        },
        onYearChange: function(selectedDates, dateStr, fp) {
            if (fp.updateCustomStats) fp.updateCustomStats();
        }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);
    return flatpickr(selector, finalOptions);
};
