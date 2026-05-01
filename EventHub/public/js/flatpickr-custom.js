/**
 * EventHub - Custom Flatpickr Initialization
 * Applies the premium dashboard design and centered modal behavior.
 */

window.initFlatpickr = function(selector, options = {}) {
    const isAr = document.documentElement.lang === 'ar';
    
    const defaultOptions = {
        dateFormat: "Y-m-d",
        locale: isAr ? 'ar' : 'en',
        onOpen: function(selectedDates, dateStr, fp) {
            // Placeholder for custom validation (e.g. check if venue is selected)
            if (options.onOpenBefore) {
                options.onOpenBefore(selectedDates, dateStr, fp);
            }
        },
        onReady: function(selectedDates, dateStr, fp) {
            // 1. Inject Premium Header
            const headerHtml = `
                <div class="fp-custom-top-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 0 4px; direction: ${isAr ? 'rtl' : 'ltr'};">
                    <button type="button" class="fp-close-btn" style="width: 38px; height: 38px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; order: ${isAr ? 2 : 1};">✕</button>
                    <div style="display: flex; align-items: center; gap: 16px; order: ${isAr ? 1 : 2};">
                        <div style="text-align: ${isAr ? 'right' : 'left'};">
                            <div style="font-size: 1.15rem; font-weight: 700; color: #e2e8f0; margin-bottom: 4px;">${isAr ? 'تقويم الحجوزات' : 'Booking Calendar'}</div>
                            <div style="font-size: 0.85rem; color: #828a99;">${isAr ? 'اضغط على أي يوم لاختياره' : 'Click on any day to select it'}</div>
                        </div>
                        <div style="width: 46px; height: 46px; background: #8b5cf6; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                </div>
            `;
            fp.calendarContainer.insertAdjacentHTML('afterbegin', headerHtml);

            // Close button functionality
            const closeBtn = fp.calendarContainer.querySelector('.fp-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => fp.close());
                closeBtn.addEventListener('mouseover', () => closeBtn.style.background = 'rgba(255,255,255,0.1)');
                closeBtn.addEventListener('mouseout', () => closeBtn.style.background = 'rgba(255,255,255,0.05)');
            }

            // 2. Inject Stats Row if data is provided
            if (options.showStats) {
                const statsHtml = `
                    <div class="fp-custom-stats" style="display: flex; flex-direction: row-reverse; gap: 12px; margin-bottom: 24px;">
                        <div style="flex: 1; background: #1a1d27; border: 1px solid #2d3342; border-radius: 8px; padding: 16px 8px; text-align: center;">
                            <div style="font-size: 0.85rem; color: #828a99; margin-bottom: 8px;">${isAr ? 'إجمالي الأيام' : 'Total Days'}</div>
                            <div class="fp-stat-total" style="font-size: 1.5rem; font-weight: 700; color: #60a5fa;">0</div>
                        </div>
                        <div style="flex: 1; background: #1a1d27; border: 1px solid #2d3342; border-radius: 8px; padding: 16px 8px; text-align: center;">
                            <div style="font-size: 0.85rem; color: #828a99; margin-bottom: 8px;">${isAr ? 'محجوز' : 'Booked'}</div>
                            <div class="fp-stat-booked" style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">0</div>
                        </div>
                        <div style="flex: 1; background: #1a1d27; border: 1px solid #2d3342; border-radius: 8px; padding: 16px 8px; text-align: center;">
                            <div style="font-size: 0.85rem; color: #828a99; margin-bottom: 8px;">${isAr ? 'متاح' : 'Available'}</div>
                            <div class="fp-stat-avail" style="font-size: 1.5rem; font-weight: 700; color: #10b981;">0</div>
                        </div>
                    </div>
                `;
                fp.calendarContainer.querySelector('.fp-custom-top-header').insertAdjacentHTML('afterend', statsHtml);
            }

            // 3. Inject Legend (Footer)
            const footerHtml = `
                <div class="fp-custom-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #2d3342; display: flex; justify-content: flex-start; gap: 16px; font-size: 0.85rem; color: #828a99; flex-direction: row-reverse; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; background: #1a1d27; border: 1px solid #2d3342; border-radius: 3px;"></span> ${isAr ? 'متاح' : 'Available'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; background: #ef4444; border-radius: 3px;"></span> ${isAr ? 'محجوز' : 'Booked'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; background: repeating-linear-gradient(45deg, rgba(245,158,11,0.6), rgba(245,158,11,0.6) 2px, rgba(239,68,68,0.6) 2px, rgba(239,68,68,0.6) 4px); border: 1px solid #f59e0b; border-radius: 3px;"></span> ${isAr ? 'صيانة' : 'Maintenance'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border: 2px solid #8b5cf6; border-radius: 3px;"></span> ${isAr ? 'اليوم' : 'Today'}</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; background: transparent; border: 1px solid #2d3342; opacity: 0.3; border-radius: 3px;"></span> ${isAr ? 'ماضي' : 'Past'}</div>
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
                    
                    // Filter bookings for the current visible month
                    const bookingsInMonth = window.currentVenueBookings.filter(b => {
                        const bDate = b.booking_date || b.date;
                        return bDate && String(bDate).startsWith(prefix);
                    });
                    
                    // Count unique dates that have at least one booking
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
