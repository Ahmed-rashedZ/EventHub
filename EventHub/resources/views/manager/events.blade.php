<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Events – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
  <script src="/js/i18n.js"></script>
  <script src="/js/flatpickr-custom.js"></script>

  <link rel="icon" href="/images/logo.png" type="image/png">
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo"
        style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img
          src="/images/logo.png?v=3" alt="EventHub Logo"
          style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
      <nav class="sidebar-nav" id="sidebar-links"></nav>
      @include('partials._sidebar-footer')
    </aside>

    <main class="main-content">
      <div class="topbar">
        <div>
          <h1 class="page-title">My Events</h1>
          <p class="page-subtitle">Create and manage your events</p>
        </div>
        <div class="topbar-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="position:relative">
            <input id="search-input" type="text" class="form-control" placeholder="Search by event name..."
              style="width:220px;padding-left:36px" oninput="applyFilter()">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"
              width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select id="filter-status" class="form-control" style="width:145px" onchange="applyFilter()">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
          <div style="position:relative;display:flex;align-items:center">
            <svg
              style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none"
              width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M7 12h10M11 18h2" />
            </svg>
            <select id="sort-events" class="form-control" style="width:190px;padding-left:32px"
              onchange="applyFilter()">
              <option value="soonest">Soonest First</option>
              <option value="farthest">Farthest First</option>
              <option value="alpha">Alphabetical</option>
              <option value="live">Live Now</option>
              <option value="ended">Ended</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="openModal()">+ Create Event</button>
        </div>
      </div>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Venue</th>
                <th>Start</th>
                <th>Capacity</th>
                <th>Sponsorships</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="events-body">
              <tr class="loading-row">
                <td colspan="8">
                  <div class="spinner" style="margin:auto"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Create Event Modal — Two-Step Wizard -->
  <div class="modal-overlay" id="event-modal">
    <div class="modal" style="max-width:600px; max-height: 90vh; overflow-y: auto; margin: 20px 0; padding-top: 0;">
      <div class="modal-header"
        style="position: sticky; top: 0; background: rgba(15,18,25,0.97); backdrop-filter: blur(16px); z-index: 10; padding: 24px 0 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="modal-title">Create New Event</h3>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>

      <!-- Step Progress Indicator -->
      <div id="wizard-progress"
        style="display: flex; align-items: center; gap: 0; padding: 20px 0 24px; position: sticky; top: 60px; background: rgba(15,18,25,0.97); backdrop-filter: blur(16px); z-index: 9;">
        <div class="wiz-step active" id="wiz-dot-1"
          style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;"
          onclick="goToStep(1)">
          <div style="display: flex; align-items: center; gap: 8px; width: 100%;">
            <div class="wiz-num" id="wiz-num-1"
              style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; flex-shrink: 0; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(139,92,246,0.3);">
              1</div>
            <div
              style="flex: 1; height: 2px; background: rgba(255,255,255,0.06); border-radius: 1px; position: relative; overflow: hidden;">
              <div id="wiz-line"
                style="position: absolute; left: 0; top: 0; height: 100%; width: 0%; background: linear-gradient(90deg, #8b5cf6, #7c3aed); border-radius: 1px; transition: width 0.4s ease;">
              </div>
            </div>
          </div>
          <span id="wiz-label-1"
            style="font-size: 0.7rem; font-weight: 600; color: #c4b5fd; letter-spacing: 0.03em; transition: color 0.3s;">Event
            Details</span>
        </div>
        <div class="wiz-step" id="wiz-dot-2"
          style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;"
          onclick="goToStep(2)">
          <div style="display: flex; align-items: center; gap: 8px; width: 100%;">
            <div class="wiz-num" id="wiz-num-2"
              style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; background: rgba(255,255,255,0.06); color: #64748b; flex-shrink: 0; transition: all 0.3s ease;">
              2</div>
            <div style="flex: 1;"></div>
          </div>
          <span id="wiz-label-2"
            style="font-size: 0.7rem; font-weight: 600; color: #64748b; letter-spacing: 0.03em; transition: color 0.3s;">Booking
            & Settings</span>
        </div>
      </div>

      <form id="event-form">
        <!-- ═══════ STEP 1: Basic Info ═══════ -->
        <div id="wizard-step-1" class="wizard-step">
          <div class="form-section">
            <div class="form-section-title">Basic Information</div>
            <div class="form-group">
              <label class="form-label">Event Title</label>
              <input id="e-title" type="text" class="form-control" placeholder="e.g. Tech Summit 2026" required />
            </div>
            <div class="form-group">
              <label class="form-label">Event Type</label>
              <select id="e-type" class="form-control" required>
                <option value="مؤتمر">
                  <script>document.write(t('Conference'))</script>
                </option>
                <option value="ندوة">
                  <script>document.write(t('Seminar'))</script>
                </option>
                <option value="ورشة عمل">
                  <script>document.write(t('Workshop'))</script>
                </option>
                <option value="دورة تدريبية">
                  <script>document.write(t('Training Course'))</script>
                </option>
                <option value="ترفيه">
                  <script>document.write(t('Entertainment'))</script>
                </option>
                <option value="ملتقى علمي">
                  <script>document.write(t('Scientific Forum'))</script>
                </option>
                <option value="رياضة">
                  <script>document.write(t('Sports'))</script>
                </option>
                <option value="تقنية">
                  <script>document.write(t('Technology'))</script>
                </option>
                <option value="اجتماعية">
                  <script>document.write(t('Social'))</script>
                </option>
                <option value="معرض">
                  <script>document.write(t('Exhibition'))</script>
                </option>
              </select>
              <div id="exhibition-hint"
                style="display:none; margin-top:8px; padding:10px; background:rgba(139,92,246,0.1); border:1px dashed rgba(139,92,246,0.3); border-radius:10px;">
                <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#c4b5fd;">
                  <span></span>
                  <p style="margin:0;">
                    <script>document.write(t('Exhibition features (booths, company applications, and contracts) will be enabled for this event.'))</script>
                  </p>
                </div>
              </div>
            </div>

            <!-- AI Description Suggestion Card -->
            <div id="ai-desc-card"
              style="display:none; margin-bottom:16px; padding:14px 16px; background: linear-gradient(135deg, rgba(139,92,246,0.08), rgba(6,182,212,0.06)); border:1px solid rgba(139,92,246,0.25); border-radius:14px; position:relative; overflow:hidden; transition: all 0.3s ease;">
              <div
                style="position:absolute; top:-20px; right:-20px; width:80px; height:80px; background:radial-gradient(circle, rgba(139,92,246,0.15), transparent); border-radius:50%;">
              </div>
              <div style="display:flex; align-items:center; gap:12px;">
                <div
                  style="width:38px; height:38px; border-radius:12px; background:linear-gradient(135deg, #8b5cf6, #06b6d4); display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; box-shadow: 0 4px 12px rgba(139,92,246,0.3);">
                  🤖</div>
                <div style="flex:1; min-width:0;">
                  <!-- Prompt State -->
                  <div id="ai-desc-prompt">
                    <div
                      style="font-size:0.72rem; font-weight:700; color:#a78bfa; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">
                      <script>document.write(t('AI Assistant'))</script>
                    </div>
                    <div style="font-size:0.85rem; color:#e2e8f0; margin-bottom:8px;">
                      <script>document.write(t('Want AI to generate a description based on your title?'))</script>
                    </div>
                    <div style="display:flex; gap:8px;">
                      <button type="button" id="ai-desc-generate-btn" onclick="generateAIDescription()"
                        style="padding:6px 16px; background:linear-gradient(135deg, #8b5cf6, #7c3aed); color:#fff; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; transition:all 0.2s; box-shadow:0 2px 8px rgba(139,92,246,0.3);">
                        <script>document.write(t('Generate'))</script>
                      </button>
                      <button type="button" onclick="dismissAIDesc()"
                        style="padding:6px 12px; background:rgba(255,255,255,0.06); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); border-radius:8px; font-size:0.8rem; cursor:pointer; transition:all 0.2s;">
                        <script>document.write(t('No thanks'))</script>
                      </button>
                    </div>
                  </div>
                  <!-- Loading State -->
                  <div id="ai-desc-loading" style="display:none;">
                    <div style="display:flex; align-items:center; gap:8px;">
                      <div class="spinner" style="width:18px; height:18px; border-width:2px;"></div>
                      <span style="font-size:0.82rem; color:#94a3b8;">
                        <script>document.write(t('Generating description...'))</script>
                      </span>
                    </div>
                  </div>
                  <!-- Success State -->
                  <div id="ai-desc-success" style="display:none;">
                    <div
                      style="font-size:0.72rem; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">
                      <script>document.write(t('Description Generated!'))</script>
                    </div>
                    <div style="font-size:0.82rem; color:#94a3b8;">
                      <script>document.write(t('The description has been filled in below. Feel free to edit it.'))</script>
                    </div>
                  </div>
                  <!-- Error State -->
                  <div id="ai-desc-error" style="display:none; font-size:0.8rem; color:#f59e0b;"></div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea id="e-desc" class="form-control" placeholder="Briefly describe your event…" required
                rows="3"></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">
                <script>document.write(t('Event Banner Image'))</script>
              </label>
              <input id="e-image" type="file" accept="image/*" class="form-control" style="padding: 7px 10px;"
                required />
              <div id="banner-preview"
                style="display:none; margin-top:10px; border-radius:10px; overflow:hidden; border:1px solid rgba(255,255,255,0.06);">
                <img id="banner-preview-img" src=""
                  style="width:100%; height:140px; object-fit:cover; display:block;" />
              </div>
            </div>
          </div>

          <div class="modal-footer"
            style="margin-top: 16px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between;">
            <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()"
              style="display:flex;align-items:center;gap:8px;">
              Continue <span style="font-size:1.1em;">→</span>
            </button>
          </div>
        </div>

        <!-- ═══════ STEP 2: Venue & Settings ═══════ -->
        <div id="wizard-step-2" class="wizard-step" style="display:none;">

          <input type="hidden" id="e-location-type" name="location_type" value="internal" />

          <div class="form-section">
            <div class="form-section-title">Venue & Schedule</div>

            <div class="form-group" style="margin-bottom: 24px;">
              <label class="form-label">Event Venue</label>

              <!-- Segmented Pill Toggle -->
              <div id="venue-toggle"
                style="display: flex; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 4px; margin-bottom: 16px; position: relative;">
                <div id="venue-toggle-indicator"
                  style="position: absolute; top: 4px; left: 4px; width: calc(50% - 4px); height: calc(100% - 8px); background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 9px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 8px rgba(139,92,246,0.3); z-index: 0;">
                </div>
                <button type="button" id="btn-internal" onclick="setLocationMode('internal')"
                  style="flex: 1; padding: 10px 16px; background: transparent; border: none; color: #fff; font-weight: 600; font-size: 0.85rem; cursor: pointer; z-index: 1; display: flex; align-items: center; justify-content: center; gap: 6px; transition: color 0.3s;">
                  Inside Exhibition
                </button>
                <button type="button" id="btn-external" onclick="setLocationMode('external')"
                  style="flex: 1; padding: 10px 16px; background: transparent; border: none; color: #64748b; font-weight: 600; font-size: 0.85rem; cursor: pointer; z-index: 1; display: flex; align-items: center; justify-content: center; gap: 6px; transition: color 0.3s;">
                  External Venue
                </button>
              </div>

              <!-- Internal: venue select -->
              <div id="venue-internal-wrap" style="display: block;">
                <div style="position: relative;">
                  <select id="e-venue" class="form-control" onchange="updatePeriodTimes()" required
                    style="padding-left: 42px; cursor: pointer; background-color: rgba(255,255,255,0.02);">
                    <option value="">Select a hall inside the exhibition...</option>
                  </select>
                  <div
                    style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.7;">
                  </div>
                </div>
              </div>

              <!-- External: venue name input -->
              <div id="venue-external-wrap" style="display: none;">
                <div style="position: relative;">
                  <input id="e-ext-name" type="text" class="form-control"
                    placeholder="External Hall Name (e.g., Corinthia Hotel)"
                    style="padding-left: 42px; background-color: rgba(255,255,255,0.02);" />
                  <div
                    style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.7;">
                  </div>
                </div>
              </div>
            </div>

            <div id="internal-fields">
              <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">
                  <script>document.write(t('Select Event Days'))</script>
                </label>
                <small style="color:var(--text-muted);font-size:12px;display:block;margin-bottom:8px;">
                  <script>document.write(t('Click on days to select them. Max span: 14 days between first and last day.'))</script>
                </small>
                <div id="e-int-calendar-wrap"
                  style="border:1px solid rgba(139,92,246,0.15);border-radius:16px;overflow:hidden;"></div>
                <input id="e-booking-date" type="text" style="display:none;" />
              </div>
              <div id="int-calendar-slots" style="margin-top:20px;display:flex;flex-direction:column;gap:12px;"></div>
            </div>

            <div id="external-fields" style="display: none;">
              <div class="form-group">
                <label class="form-label">
                  <script>document.write(t('Google Maps Link'))</script>
                </label>
                <input id="e-ext-location" type="url" class="form-control" placeholder="https://maps.google.com/..." />
              </div>
              <div class="form-group">
                <label class="form-label">
                  <script>document.write(t('Proof of Booking (PDF/Image)'))</script>
                </label>
                <input id="e-booking-proof" type="file" accept=".pdf,image/*" class="form-control"
                  style="padding:7px 10px;" />
                <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">
                  <script>document.write(t('Required. Upload official confirmation of the external booking.'))</script>
                </small>
              </div>

              <!-- Multi-day Calendar -->
              <div class="form-group">
                <label class="form-label">
                  <script>document.write(t('Select Event Days'))</script>
                </label>
                <small style="color:var(--text-muted);font-size:12px;display:block;margin-bottom:8px;">
                  <script>document.write(t('Click on days to select them. Max span: 14 days between first and last day.'))</script>
                </small>
                <div id="e-ext-calendar-wrap"
                  style="border:1px solid rgba(139,92,246,0.15);border-radius:16px;overflow:hidden;"></div>
              </div>

              <!-- Time slots container -->
              <div id="ext-schedule-slots" style="display:none;">
                <label class="form-label" style="margin-bottom:10px;">⏰ Set Times for Each Day</label>
                <div id="ext-slots-list" style="display:flex;flex-direction:column;gap:10px;"></div>
              </div>
            </div>
          </div>

          <div class="form-section" style="margin-bottom: 0;">
            <div class="form-section-title">Additional Settings</div>
            <div class="form-group">
              <label class="form-label">Event Objective</label>
              <textarea id="e-objective" class="form-control" placeholder="What is the main goal of this event?"
                rows="2" required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Target Audience</label>
              <input id="e-audience" type="text" class="form-control"
                placeholder="e.g. Students, Professionals, General Public" required />
            </div>
            <!-- AI Attendance Prediction Card -->
            <div id="ai-prediction-card"
              style="display:none; margin-bottom:16px; padding:16px 18px; background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(6,182,212,0.06)); border:1px solid rgba(16,185,129,0.25); border-radius:14px; position:relative; overflow:hidden;">
              <div
                style="position:absolute; top:-20px; right:-20px; width:80px; height:80px; background:radial-gradient(circle, rgba(16,185,129,0.15), transparent); border-radius:50%;">
              </div>
              <div style="display:flex; align-items:flex-start; gap:12px;">
                <div
                  style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #10b981, #06b6d4); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                </div>
                <div style="flex:1; min-width:0;">
                  <div
                    style="font-size:0.7rem; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    <script>document.write(t('AI Attendance Prediction'))</script>
                  </div>
                  <div id="ai-prediction-loading" style="display:none;">
                    <div style="display:flex; align-items:center; gap:8px;">
                      <div class="spinner" style="width:18px; height:18px; border-width:2px;"></div>
                      <span style="font-size:0.8rem; color:#94a3b8;">
                        <script>document.write(t('Analyzing event data...'))</script>
                      </span>
                    </div>
                  </div>
                  <div id="ai-prediction-result" style="display:none;">
                    <div style="font-size:1.4rem; font-weight:800; color:#fff; line-height:1.2;">
                      <script>document.write(t('Expected Attendance:'))</script> <span id="ai-predicted-number"
                        style="color:#10b981;"></span>
                    </div>
                    <div id="ai-prediction-hint"
                      style="font-size:0.78rem; color:#94a3b8; margin-top:6px; line-height:1.4;"></div>
                  </div>
                  <div id="ai-prediction-error" style="display:none; font-size:0.8rem; color:#f59e0b;"></div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Capacity</label>
              <div
                style="display:flex; gap:15px; margin-bottom:10px; background:rgba(255,255,255,0.03); padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.05);">
                <label
                  style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:#fff;">
                  <input type="radio" name="capacity_type" value="fixed" checked
                    onchange="toggleCapacityInput('create', this.value)"
                    style="width:16px; height:16px; accent-color:#8b5cf6;">
                  <script>document.write(t('Fixed Number'))</script>
                </label>
                <label
                  style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:#fff;">
                  <input type="radio" name="capacity_type" value="unlimited"
                    onchange="toggleCapacityInput('create', this.value)"
                    style="width:16px; height:16px; accent-color:#8b5cf6;">
                  <script>document.write(t('Unlimited'))</script> (مفتوح)
                </label>
              </div>
              <div id="capacity-input-wrap">
                <input id="e-capacity" type="number" class="form-control" placeholder="200" min="1" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">
                <script>document.write(t('Competent Authority Approval'))</script>
              </label>
              <input id="e-ministry-doc" type="file" accept=".pdf,image/*" class="form-control"
                style="padding: 7px 10px;" required />
              <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">
                <script>document.write(t('Required. Upload the official approval from the relevant competent authority for this event.'))</script>
              </small>
            </div>
          </div>

          <!-- Agenda Section -->
          <div class="form-section" style="margin-bottom: 0;">
            <div class="form-section-title">
              <script>document.write(t('Event Agenda'))</script>
              <small style="font-weight:400;color:var(--danger);font-size:0.75rem;">
                <script>document.write(t('(Required)'))</script>
              </small>
            </div>
            <small style="color:var(--text-muted);font-size:12px;display:block;margin-bottom:12px;">
              <script>document.write(t('Define the schedule/program for your event. You must add at least one agenda item.'))</script>
            </small>

            <div id="agenda-items-create" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="addAgendaItem('agenda-items-create')"
              style="display:flex;align-items:center;gap:6px;">
              <span style="font-size:1.1rem;">+</span>
              <script>document.write(t('Add Agenda Item'))</script>
            </button>
          </div>

          <div class="modal-footer"
            style="margin-top: 16px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between;">
            <button type="button" class="btn btn-ghost" onclick="prevStep()"
              style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:1.1em;">←</span> Back
            </button>
            <button type="submit" class="btn btn-primary" style="display:flex;align-items:center;gap:8px;">
              Submit for Approval
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Pending Event Modal -->
  <div class="modal-overlay" id="edit-modal">
    <div class="modal" style="max-width:520px; max-height: 85vh; overflow-y: auto; margin: 20px 0; padding-top: 0;">
      <div class="modal-header"
        style="position: sticky; top: 0; background: rgba(15,18,25,0.97); backdrop-filter: blur(16px); z-index: 10; padding: 24px 0 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="modal-title">Edit Event</h3>
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
      </div>

      <!-- Review message banner -->
      <div id="edit-review-banner"
        style="margin: 16px 0; padding: 12px 16px; background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.15); border-radius: 10px;">
        <div
          style="font-size:0.65rem;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">
          <script>document.write(t("Admin's Review"))</script>
        </div>
        <div id="edit-review-msg" style="font-size:0.75rem;color:#e2e8f0;line-height:1.4;"></div>
      </div>

      <form id="edit-form" onsubmit="submitEdit(event)">
        <input type="hidden" id="edit-event-id" />
        <div id="edit-fields-container" class="form-section" style="margin-bottom:0;">
          <!-- Dynamic fields will be injected here -->
        </div>
        <div class="modal-footer"
          style="margin-top: 16px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between;">
          <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
          <button type="submit" class="btn btn-primary" style="display:flex;align-items:center;gap:6px;">
            Submit Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    function getContactIcon(type) {
      const svgStyle = 'width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; display:block;';
      switch (type.toLowerCase()) {
        case 'email':
        case 'contact_email':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>`;
        case 'phone':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2v3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>`;
        case 'twitter':
        case 'x':
          return `<span style="font-weight: 800; font-family: sans-serif;">𝕏</span>`;
        case 'linkedin':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>`;
        case 'website':
        case 'globe':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>`;
        case 'portfolio':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>`;
        case 'facebook':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>`;
        case 'instagram':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>`;
        case 'whatsapp':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>`;
        case 'telegram':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>`;
        case 'github':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>`;
        case 'youtube':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>`;
        case 'tiktok':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>`;
        case 'discord':
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M8 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2zm8 0c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"></path><path d="M12 14c-2 0-4-1-4-1v2s2 1 4 1 4-1 4-1v-2s-2 1-4 1z"></path></svg>`;
        default:
          return `<svg style="${svgStyle}" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>`;
      }
    }

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
        minDate: new Date().fp_incr(60),
        showStats: true,
        disable: [
          function (date) {
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
        onChange: function () {
          checkAvailability();
        },
        onOpenBefore: function (selectedDates, dateStr, fp) {
          const venueSelect = document.getElementById('e-venue');
          const locationType = document.getElementById('e-location-type').value;
          if (locationType === 'internal' && !venueSelect.value) {
            setTimeout(() => fp.close(), 0);
            showToast(document.documentElement.lang === 'ar' ? 'يرجى اختيار القاعة أولاً.' : 'Please select a venue first.', 'info');
          }
        },
        onDayCreate: function (dObj, dStr, fp, dayElem) {
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
                tooltip.textContent = reason;
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

      const _ePeriodEl = document.getElementById('e-period');
      if (_ePeriodEl) {
        _ePeriodEl.addEventListener('change', function (e) {
          const selectedOption = this.options[this.selectedIndex];
          if (selectedOption && selectedOption.getAttribute('data-booked') === 'true') {
            showToast(document.documentElement.lang === 'ar' ? 'هذه الفترة محجوزة مسبقاً، يرجى اختيار فترة أخرى.' : 'This period is already booked, please choose another.', 'error');
            this.value = '';
          }
        });
      }

      document.addEventListener('click', function (e) {
        if (e.target.classList.contains('flatpickr-day') && e.target.classList.contains('flatpickr-disabled')) {
          if (e.target.classList.contains('date-maintenance')) {
            const reason = e.target.getAttribute('data-maint-reason');
            const isAr = document.documentElement.lang === 'ar';
            let msg = isAr ? 'هذا التاريخ محجوز للصيانة' : 'This date is reserved for maintenance';
            if (reason) {
              msg += isAr ? ` (السبب: ${reason})` : ` (Reason: ${reason})`;
            }
            msg += isAr ? '، يرجى اختيار تاريخ آخر.' : ', please choose another date.';
            showToast(msg, 'error');
          } else if (e.target.classList.contains('date-fully-booked')) {
            showToast(document.documentElement.lang === 'ar' ? 'هذا التاريخ محجوز بالكامل، يرجى اختيار تاريخ آخر.' : 'This date is fully booked, please choose another.', 'error');
          } else {
            showToast(document.documentElement.lang === 'ar' ? 'لا يمكنك حجز هذا التاريخ. يجب أن يكون الحجز بعد 60 يوماً من اليوم على الأقل.' : 'You cannot book this date. Bookings must be made at least 60 days in advance.', 'info');
          }
        }
      }, true);
    }

    async function loadEvents() {
      try {
        const res = await api.get('/events/list/my');
        const tbody = document.getElementById('events-body');
        if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
        allEvents = res.data;
        applyFilter();
      } catch (err) {
        console.error('Error loading events:', err);
        const tbody = document.getElementById('events-body');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load events</td></tr>';
      }
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
      if (!events.length) { tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg></div><p>No events found</p></div></td></tr>'; return; }
      tbody.innerHTML = events.map((ev, i) => {
        const reviewBadge = ev.review_status === 'needs_review'
          ? `<button onclick="toggleReviewRow(${ev.id})" style="display:inline-flex;align-items:center;gap:4px;background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:8px;font-size:0.7rem;font-weight:600;border:1px solid rgba(245,158,11,0.25);cursor:pointer;outline:none;">Review Required</button>`
          : ev.review_status === 'reviewed'
            ? `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(59,130,246,0.12);color:#3b82f6;padding:2px 8px;border-radius:8px;font-size:0.7rem;font-weight:600;border:1px solid rgba(59,130,246,0.25);">Updated</span>`
            : '';

        const reviewRow = ev.review_status === 'needs_review' && ev.review_message
          ? `<tr id="review-row-${ev.id}" style="display:none;"><td colspan="8" style="padding:0;border:none;">
              <div style="margin:0 16px 12px; padding:12px 16px; background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.15); border-radius:10px; display:flex; align-items:center; gap:12px;">
                <div style="flex:1;">
                  <div style="font-size:0.65rem;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">${t('Admin Review')}</div>
                  <div style="font-size:0.75rem;color:#e2e8f0;line-height:1.4;">${ev.review_message}</div>
                  <div style="font-size:0.65rem;color:#94a3b8;margin-top:4px;">${t('Fields to update:')} ${(ev.review_fields || []).map(f => `<span style="background:rgba(255,255,255,0.06);padding:1px 6px;border-radius:4px;margin-right:4px;">${t(f) || f}</span>`).join('')}</div>
                </div>
                <button class="btn btn-sm" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);white-space:nowrap;" onclick="openEditModal(${ev.id})">Edit</button>
              </div>
            </td></tr>`
          : '';

        return `
        <tr>
          <td style="color:var(--text-muted)">${i + 1}</td>
          <td><div style="font-weight:600" class="i18n-skip">${ev.title}</div></td>
          <td style="color:var(--text-muted)">${ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' (' + t('External') + ')' : '—')}</td>
          <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
          <td style="color:var(--text-muted)">${ev.capacity || t('Unlimited')}</td>
          <td>
             <div style="display:flex; align-items:center; margin-bottom: ${ev.is_exhibition ? '8px' : '0'}">
               <input type="checkbox" id="spon-tog-${ev.id}" ${ev.is_sponsorship_open ? 'checked' : ''} onchange="toggleSponsorship(${ev.id}, this.checked)" 
                 style="width:16px; height:16px; margin-right:5px; ${(ev.status !== 'approved' || ev.time_status === 'live' || ev.time_status === 'ended') ? 'cursor:not-allowed;' : 'cursor:pointer;'}" 
                 ${(ev.status !== 'approved' || ev.time_status === 'live' || ev.time_status === 'ended') ? 'disabled' : ''}/>
               <label for="spon-tog-${ev.id}" style="font-size:11px; ${(ev.status !== 'approved' || ev.time_status === 'live' || ev.time_status === 'ended') ? 'color:var(--text-muted); cursor:not-allowed;' : 'cursor:pointer;'}">${t('Sponsorship')}</label>
             </div>
             ${ev.is_exhibition ? `
             <div style="display:flex; align-items:center;">
               <input type="checkbox" id="exh-tog-${ev.id}" ${ev.is_exhibitor_registration_open ? 'checked' : ''} onchange="toggleExhibitorRegistration(${ev.id}, this.checked)" 
                 style="width:16px; height:16px; margin-right:5px; ${(ev.status !== 'approved' || ev.time_status === 'ended') ? 'cursor:not-allowed;' : 'cursor:pointer;'}" 
                 ${(ev.status !== 'approved' || ev.time_status === 'ended') ? 'disabled' : ''}/>
               <label for="exh-tog-${ev.id}" style="font-size:11px; ${(ev.status !== 'approved' || ev.time_status === 'ended') ? 'color:var(--text-muted); cursor:not-allowed;' : 'cursor:pointer;'}">${t('Register')}</label>
             </div>
             ` : ''}
             ${ev.status !== 'approved' ? `<div style="font-size:10px;color:#ef4444;margin-top:4px;">${t('Needs Approval')}</div>` : ''}
             ${ev.status === 'approved' && (ev.time_status === 'live' || ev.time_status === 'ended') ? `<div style="font-size:10px;color:var(--text-muted);margin-top:4px;">${t(ev.time_status === 'live' ? 'Event is live' : 'Event has ended')}</div>` : ''}
          </td>
          <td><div style="display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center;">${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''} ${reviewBadge}</div></td>
          <td style="padding:14px 16px;">
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
              <button class="btn btn-sm" style="background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.25)" onclick="showEventDetails(${ev.id})">${t('Details')}</button>
              <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/manager/event-stats/${ev.id}'">${t('Stats')}</button>
              ${ev.status === 'approved' && ev.time_status !== 'ended' ? `<button class="btn btn-sm" style="background:${ev.is_published ? 'rgba(16,185,129,0.12)' : 'rgba(139,92,246,0.12)'};color:${ev.is_published ? '#10b981' : '#a78bfa'};border:1px solid ${ev.is_published ? 'rgba(16,185,129,0.25)' : 'rgba(139,92,246,0.25)'}" onclick="openPublishedScheduleModal(${ev.id})">${t('Publish Days')}</button>` : ''}
              ${ev.status === 'pending' ? `<button class="btn btn-sm" style="background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.25)" onclick="deleteEvent(${ev.id})">${t('Delete')}</button>` : ''}
            </div>
          </td>
        </tr>${reviewRow}`;
      }).join('');
    }

    // Modal for event details
    function toggleReviewRow(id) {
      const row = document.getElementById('review-row-' + id);
      if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
      }
    }

    const typeIcons = { 'مؤتمر': '', 'ندوة': '', 'ورشة عمل': '', 'دورة تدريبية': '', 'ترفيه': '', 'ملتقى علمي': '', 'رياضة': '', 'تقنية': '', 'اجتماعية': '', 'معرض': '', 'Other': '' };
    const typeColors = {
      'مؤتمر': '#3b82f6', 'Conference': '#3b82f6',
      'ندوة': '#8b5cf6', 'Seminar': '#8b5cf6',
      'ورشة عمل': '#10b981', 'Workshop': '#10b981',
      'دورة تدريبية': '#06b6d4', 'Training Course': '#06b6d4',
      'ترفيه': '#ec4899', 'Entertainment': '#ec4899',
      'ملتقى علمي': '#f59e0b', 'Scientific Forum': '#f59e0b',
      'رياضة': '#22c55e', 'Sports': '#22c55e',
      'تقنية': '#6366f1', 'Technology': '#6366f1',
      'اجتماعية': '#f97316', 'Social': '#f97316',
      'معرض': '#f43f5e', 'Exhibition': '#f43f5e',
      'Other': '#64748b'
    };

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
          content.innerHTML = '<div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; color:var(--danger);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div><p>Could not fetch event details</p></div>';
          return;
        }
        const ev = res.data;
        const reviewData = revRes.ok ? revRes.data : { average_rating: 0, reviews: [] };
        const eType = ev.event_type || 'Other';
        const tColor = typeColors[eType] || typeColors.Other || '#64748b';

        const bannerSection = ev.image
          ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
          : `<div class="ed-banner ed-banner-placeholder"><div class="ed-banner-fade"></div></div>`;

        const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
          ? `<div class="ed-rejection"><span class="ed-rej-label">Rejection Reason</span><p class="i18n-skip">${ev.rejection_reason}</p></div>`
          : '';

        let sponsorsHtml = '';
        if (ev.sponsors && ev.sponsors.length > 0) {
          const getTierBadge = (tier) => {
            switch (tier) {
              case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 8px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#128142; Diamond</span>';
              case 'gold': return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 8px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129351; Gold</span>';
              case 'silver': return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 8px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129352; Silver</span>';
              case 'bronze': return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 8px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129353; Bronze</span>';
              default: return `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 8px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#9898; ${tier || 'Sponsor'}</span>`;
            }
          };

          sponsorsHtml = `
          <div class="ed-section mt-4" style="margin-top: 16px;">
            <div class="ed-section-label">Current Sponsors</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
              ${ev.sponsors.filter(sp => sp).map(sp => `
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

        // Build Exhibitors section
        let exhibitorsHtml = '';
        if (ev.exhibitors && ev.exhibitors.length > 0) {
          const exItems = ev.exhibitors.filter(ex => ex).map(ex => {
            const user = ex.company || {};
            const profile = user.profile || {};
            const name = profile.company_name || user.name || '—';
            const letter = name.charAt(0).toUpperCase();
            const rawLogo = profile.logo;
            const logo = rawLogo ? ((rawLogo.startsWith('http') || rawLogo.startsWith('/')) ? rawLogo : '/storage/' + rawLogo) : null;
            const avatarHtml = logo ? `<img src="${logo}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML='<span style=\'font-size:15px;\'>${letter}</span>';">` : `<span style="font-size:15px;">${letter}</span>`;
            return `
              <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.03);padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.06);cursor:pointer;transition:all 0.2s;" 
                   onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='rgba(255,255,255,0.12)'" 
                   onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.06)'" 
                   onclick="navigateToProfile(${ex.company_id})">
                <div style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;background:var(--accent-gradient);border-radius:50%;overflow:hidden;font-weight:700;color:#fff;flex-shrink:0;box-shadow:0 4px 10px rgba(0,0,0,0.2);">
                  ${avatarHtml}
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:0.95rem;font-weight:600;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${name}</div>
                </div>
              </div>`;
          }).join('');
          exhibitorsHtml = `
            <div style="margin-top:20px;">
              <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent2);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                ${t('Participating Companies')} (${ev.exhibitors.length})
              </div>
              <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:10px;">
                ${exItems}
              </div>
            </div>`;
        }

        content.innerHTML = `
      ${bannerSection}
      <div class="ed-body">

        <!-- Header: Title + Type + Badges -->
        <div class="ed-header">
          <div class="ed-title-row">
            <h2 class="ed-title i18n-skip">${ev.title}</h2>
            <span class="ed-type-pill" style="--tcolor:${tColor}">${eType}</span>
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
          <p class="ed-description i18n-skip">${ev.description || 'No description provided.'}</p>
        </div>

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5M3 21h18M10.5 8.25h3"></path></svg></div>
              <div>
                <div class="ed-info-label">Venue</div>
                <div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div>
              </div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z"></path></svg></div>
              <div>
                <div class="ed-info-label">${t('Location')}</div>
                <div class="ed-info-value">
                  ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>`
            : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` : '—')}
                </div>
              </div>
            </div>
          ${!ev.venue_id && ev.booking_proof_path ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l4.5-4.5m.718-2.262a9 9 0 019-9"></path></svg></div>
            <div><div class="ed-info-label">${t('Booking Proof')}</div><div class="ed-info-value"><button onclick="downloadEventDoc(${ev.id}, 'booking_proof')" style="color:#22d3ee;text-decoration:underline;background:none;border:none;padding:0;font:inherit;cursor:pointer;">${t('View Document')} ↗</button></div></div>
          </div>
          ` : ''}
          ${ev.ministry_document_path ? `
          <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg></div>
            <div><div class="ed-info-label">${t('Competent Authority Approval')}</div><div class="ed-info-value"><button onclick="downloadEventDoc(${ev.id}, 'ministry_document')" style="color:#a78bfa;text-decoration:underline;background:none;border:none;padding:0;font:inherit;cursor:pointer;">${t('View Document')} ↗</button></div></div>
          </div>
          ` : `
          <div class="ed-info-card ed-info-danger" style="grid-column: 1 / -1;">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            <div><div class="ed-info-label">${t('Competent Authority Approval')}</div><div class="ed-info-value" style="color:#ef4444;">${t('Not uploaded')}</div></div>
          </div>
          `}
          ${ev.event_objective ? `
          <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div><div class="ed-info-label">Event Objective</div><div class="ed-info-value" style="font-size:0.9rem;">${ev.event_objective}</div></div>
          </div>
          ` : ''}
          ${ev.target_audience ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg></div>
            <div><div class="ed-info-label">Target Audience</div><div class="ed-info-value" style="font-size:0.9rem;">${ev.target_audience}</div></div>
          </div>
          ` : ''}
          ${(function () {
            const schedule = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule :
              (ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule :
                (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null));
            if (schedule) {
              const dn = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
              const mn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
              let scheduleHtml = '<div style="grid-column: 1 / -1;">';
              scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">Event Schedule (' + schedule.length + ' day' + (schedule.length > 1 ? 's' : '') + ')</div>';
              scheduleHtml += '<div style="display:flex;flex-direction:column;gap:6px;">';
              schedule.forEach(function (slot) {
                const d = new Date(slot.date + 'T00:00:00');
                scheduleHtml += '<div style="display:flex;align-items:center;gap:10px;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.15);border-radius:10px;padding:10px 14px;">';
                scheduleHtml += '<div style="min-width:42px;text-align:center;background:rgba(139,92,246,0.12);border-radius:8px;padding:5px 4px;">';
                scheduleHtml += '<div style="font-size:0.55rem;font-weight:700;color:#a78bfa;text-transform:uppercase;">' + dn[d.getDay()] + '</div>';
                scheduleHtml += '<div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">' + d.getDate() + '</div>';
                scheduleHtml += '<div style="font-size:0.5rem;color:#94a3b8;">' + mn[d.getMonth()] + '</div>';
                scheduleHtml += '</div>';
                scheduleHtml += '<div style="flex:1;display:flex;align-items:center;gap:8px;">';
                if (slot.period && !slot.start_time) {
                  scheduleHtml += '<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;text-transform:capitalize;">' + slot.period.replace('_', ' ') + '</span>';
                }
                if (slot.start_time) {
                  scheduleHtml += '<span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">' + slot.start_time + '</span>';
                  scheduleHtml += '<span style="color:#64748b;font-size:0.8rem;">→</span>';
                }
                if (slot.end_time) {
                  scheduleHtml += '<span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">' + slot.end_time + '</span>';
                }
                scheduleHtml += '</div></div>';
              });
              scheduleHtml += '</div></div>';
              return scheduleHtml;
            } else {
              return '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div><div class="ed-info-label">Start</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div><div class="ed-info-label">End</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
            }
          })()}
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
            <div>
              <div class="ed-info-label" style="display:flex;align-items:center;gap:6px;">
                ${t('Capacity')}
                ${ev.status === 'approved' && ev.time_status !== 'ended' ? `<button type="button" class="btn-icon-sm" onclick="event.stopPropagation(); expandCapacity(${ev.id}, ${ev.capacity || 'null'}, ${ev.venue?.capacity || 99999})" title="Expand Capacity" style="padding:5px;background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;margin-inline-start:4px;position:relative;z-index:10;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg></button>` : ''}
              </div>
              <div class="ed-info-value" id="det-capacity-${ev.id}">${ev.capacity || (document.documentElement.lang === 'ar' ? 'مفتوح' : 'Unlimited')}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg></div>
            <div>
              <div class="ed-info-label">Tickets Booked</div>
              <div class="ed-info-value">${ev.tickets_count ?? '—'}</div>
            </div>
          </div>
          </div>
          
          ${sponsorsHtml}

          ${exhibitorsHtml}

          <!-- Agenda Section -->
          ${(() => {
            let agenda = ev.agenda;
            const hasAgenda = agenda && typeof agenda === 'object' && (Array.isArray(agenda) ? agenda.length > 0 : Object.keys(agenda).length > 0);
            let agendaHtml = '';
            if (hasAgenda) {
              // Filter agenda by published_schedule if it exists
              const pubDates = ev.published_schedule && ev.published_schedule.length > 0
                ? ev.published_schedule.map(p => p.date) : null;

              if (pubDates && typeof agenda === 'object' && !Array.isArray(agenda)) {
                const filtered = {};
                Object.keys(agenda).forEach(dateStr => {
                  if (pubDates.includes(dateStr)) {
                    filtered[dateStr] = agenda[dateStr];
                  }
                });
                agenda = filtered;
              }

              const hasFilteredAgenda = typeof agenda === 'object' && !Array.isArray(agenda)
                ? Object.keys(agenda).length > 0 : (Array.isArray(agenda) && agenda.length > 0);

              if (!hasFilteredAgenda) return '';

              agendaHtml += `<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;display:flex;align-items:center;gap:6px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Event Agenda</div>`;
              if (typeof agenda === 'object' && !Array.isArray(agenda)) {
                const dn = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const mn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                Object.keys(agenda).sort().forEach(dateStr => {
                  const items = agenda[dateStr];
                  if (!items || items.length === 0) return;
                  const d = new Date(dateStr + 'T00:00:00');
                  const dayLabel = `${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}`;
                  agendaHtml += `<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-flex;align-items:center;gap:6px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg> ${dayLabel}</div><div style="display:flex;flex-direction:column;gap:4px;">`;
                  items.forEach(a => {
                    agendaHtml += `<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                          <div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div>
                          <div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div>
                        </div>
                        ${a.description ? `<div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;padding-inline-start:12px;border-inline-start:2px solid rgba(34,211,238,0.2);text-align:start;line-height:1.4;">${a.description}</div>` : ''}
                      </div>`;
                  });
                  agendaHtml += `</div></div>`;
                });
              } else if (Array.isArray(agenda)) {
                agendaHtml += `<div style="display:flex;flex-direction:column;gap:4px;">`;
                agenda.forEach(a => {
                  agendaHtml += `<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;">
                      <div style="display:flex;align-items:center;gap:10px;">
                        <div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div>
                        <div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div>
                      </div>
                      ${a.description ? `<div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;padding-inline-start:12px;border-inline-start:2px solid rgba(34,211,238,0.2);text-align:start;line-height:1.4;">${a.description}</div>` : ''}
                    </div>`;
                });
                agendaHtml += `</div>`;
              }
              agendaHtml += `</div>`;
            }
            return agendaHtml;
          })()}

          <!-- Agenda Management Button (Manager only) -->
          ${ev.time_status !== 'ended' ? `
          <div style="margin-top:12px;display:flex;justify-content:center;">
            <button class="btn btn-sm" style="background:rgba(34,211,238,0.1);color:#22d3ee;border:1px solid rgba(34,211,238,0.2);display:flex;align-items:center;gap:6px;" onclick="openAgendaEditor(${ev.id})">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> ${t('Edit Agenda')}
            </button>
          </div>
          ` : ''}

          <!-- Footer -->
          <div class="ed-footer" style="margin-top: 16px; padding-bottom: 12px; display: flex; flex-direction: column; gap: 12px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
            
            <!-- Management Controls -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%; margin-top: 12px;">
              ${ev.status === 'approved' && ev.time_status !== 'ended' ? `
                <button class="btn btn-sm ${ev.is_tickets_open ? 'btn-danger' : 'btn-success'}" 
                        style="flex: 1; min-width: 140px; justify-content: center;" 
                        onclick="toggleTicketSales(${ev.id})">
                  ${ev.is_tickets_open ? t('Close Ticket Sales') : t('Open Ticket Sales')}
                </button>
                <button class="btn btn-sm btn-danger" 
                        style="flex: 1; min-width: 140px; justify-content: center; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);" 
                        onclick="openCancellationModal(${ev.id})">
                  ${t('Request Cancellation')}
                </button>
              ` : ''}

              ${ev.status === 'cancellation_requested' ? `
                <div style="width: 100%; padding: 12px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px;">
                  <div style="font-size: 0.75rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; margin-bottom: 4px;">⌛ Cancellation Pending</div>
                  <div style="font-size: 0.85rem; color: #e2e8f0;">Your request is being reviewed by the administrator.</div>
                  ${ev.cancellation_reason ? `<div style="font-size: 0.8rem; color: #94a3b8; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(245, 158, 11, 0.1);"><strong>Reason:</strong> ${ev.cancellation_reason}</div>` : ''}
                </div>
              ` : ''}

              ${ev.status === 'approved' && ev.cancellation_rejection_reason ? `
                <div style="width: 100%; padding: 12px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px;">
                  <div style="font-size: 0.75rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Cancellation Rejected</div>
                  <div style="font-size: 0.85rem; color: #e2e8f0;">The admin rejected your cancellation request.</div>
                  <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(239, 68, 68, 0.1);"><strong>Admin Note:</strong> <span class="i18n-skip">${ev.cancellation_rejection_reason}</span></div>
                </div>
              ` : ''}
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="ed-footer-label">Created by</span>
                <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
              </div>
              <div style="display: flex; align-items: center; gap: 6px;">
                 <span style="font-size: 0.7rem; color: var(--text-muted);">ID: #${ev.id}</span>
              </div>
            </div>
          </div>

      </div>
    `;
      }).catch(err => {
        console.error('Error loading event details:', err);
        showToast(t('Error loading event details'), 'error');
        closeEventDetailsModal();
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

    window.currentAgendaEditorDay = null;

    function filterAgendaEditorByDay(dayStr) {
      window.currentAgendaEditorDay = dayStr;

      // Update tabs styling
      document.querySelectorAll('.agenda-editor-tab').forEach(tab => {
        if (tab.dataset.day === dayStr) {
          tab.style.background = 'rgba(139,92,246,0.15)';
          tab.style.borderColor = 'rgba(139,92,246,0.3)';
          tab.style.color = '#c4b5fd';
        } else {
          tab.style.background = 'transparent';
          tab.style.borderColor = 'rgba(255,255,255,0.1)';
          tab.style.color = '#94a3b8';
        }
      });

      // Show/hide items
      const items = document.querySelectorAll('#agenda-items-editor .agenda-item');
      items.forEach(item => {
        const sel = item.querySelector('.agenda-date');
        if (sel && sel.value === dayStr) {
          item.style.display = 'flex';
        } else {
          item.style.display = 'none';
        }
      });
    }

    async function openAgendaEditor(eventId) {
      agendaEditingEventId = eventId;
      const container = document.getElementById('agenda-items-editor');
      container.innerHTML = '';

      const tabsContainer = document.getElementById('agenda-editor-tabs');
      if (tabsContainer) tabsContainer.innerHTML = '';

      // Fetch current event data
      const res = await api.get(`/events/${eventId}`);
      if (!res.ok) { showToast(t('Error loading event'), 'error'); return; }

      agendaEventData = res.data;

      const schedule = agendaEventData.external_schedule?.length > 0 ? agendaEventData.external_schedule : agendaEventData.internal_schedule;
      if (schedule && Array.isArray(schedule)) {
        agendaDays = schedule.map(s => s.date).sort();
      } else {
        agendaDays = agendaEventData.agenda ? Object.keys(agendaEventData.agenda) : [];
      }

      // Build tabs
      if (tabsContainer) {
        const isAr = document.documentElement.lang === 'ar';
        const dayNames = isAr ? ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        agendaDays.forEach(dStr => {
          const d = new Date(dStr + 'T00:00:00');
          const label = `${dayNames[d.getDay()]} ${d.getDate()}`;
          const btn = document.createElement('button');
          btn.className = 'agenda-editor-tab btn btn-sm';
          btn.dataset.day = dStr;
          btn.textContent = label;
          btn.style.cssText = 'border-radius:20px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:#94a3b8; padding:4px 12px; font-size:0.75rem; white-space:nowrap; cursor:pointer; transition:all 0.2s;';
          btn.onclick = () => filterAgendaEditorByDay(dStr);
          tabsContainer.appendChild(btn);
        });
      }

      // Load existing agenda
      const existing = agendaEventData.agenda;
      if (existing && typeof existing === 'object' && !Array.isArray(existing)) {
        Object.keys(existing).forEach(day => {
          const items = existing[day];
          if (Array.isArray(items)) {
            items.forEach(item => {
              item.date = day;
              addAgendaItem('agenda-items-editor', item);
            });
          }
        });
      } else if (Array.isArray(existing) && existing.length > 0) {
        if (agendaDays.length > 0) {
          existing.forEach(item => {
            item.date = agendaDays[0];
            addAgendaItem('agenda-items-editor', item);
          });
        }
      }

      document.getElementById('agenda-editor-modal').classList.add('open');

      if (agendaDays.length > 0) {
        filterAgendaEditorByDay(agendaDays[0]);
      }
    }

    function closeAgendaEditor() {
      document.getElementById('agenda-editor-modal').classList.remove('open');
      document.getElementById('agenda-items-editor').innerHTML = '';
      agendaEditingEventId = null;
      agendaEventData = null;
      agendaDays = [];
    }

    async function saveAgenda() {
      if (!agendaEditingEventId) return;

      const cleanAgenda = {};
      const items = document.querySelectorAll('#agenda-items-editor .agenda-item');
      items.forEach(item => {
        const date = item.querySelector('.agenda-date').value;
        const title = item.querySelector('.agenda-title').value.trim();
        const startTime = item.querySelector('.agenda-start').value;
        const endTime = item.querySelector('.agenda-end').value;
        const description = item.querySelector('.agenda-desc') ? item.querySelector('.agenda-desc').value.trim() : '';

        if (date && title && startTime && endTime) {
          if (!cleanAgenda[date]) cleanAgenda[date] = [];
          cleanAgenda[date].push({ title, start_time: startTime, end_time: endTime, description });
        }
      });

      // Validate bounds and logic
      let isValid = true;
      const isAr = document.documentElement.lang === 'ar';
      for (const day of Object.keys(cleanAgenda)) {
        let startBound = "00:00", endBound = "23:59";
        const schedule = agendaEventData?.external_schedule?.length > 0 ? agendaEventData.external_schedule : agendaEventData?.internal_schedule;
        if (schedule) {
          const daySched = schedule.find(s => s.date === day);
          if (daySched) {
            startBound = daySched.start_time || daySched.start;
            endBound = daySched.end_time || daySched.end;
          }
        }

        const items = cleanAgenda[day] || [];
        items.sort((a, b) => a.start_time.localeCompare(b.start_time));

        for (let i = 0; i < items.length; i++) {
          const item = items[i];
          if (item.start_time >= item.end_time) {
            showToast(isAr ? `وقت غير صالح في ${day}. وقت البدء يجب أن يكون قبل وقت الانتهاء.` : `Invalid time in ${day}. Start time must be before end time.`, 'error');
            isValid = false;
            break;
          }
          if (item.start_time < startBound || item.end_time > endBound) {
            showToast(isAr ? `وقت غير صالح في ${day}. الاجندة يجب ان تكون بين ${startBound} و ${endBound}` : `Invalid time in ${day}. Agenda must be strictly between ${startBound} and ${endBound}`, 'error');
            isValid = false;
            break;
          }
          if (i > 0 && item.start_time < items[i - 1].end_time) {
            showToast(isAr ? `عناصر جدول الأعمال المتداخلة في ${day} غير مسموحة.` : `Overlapping agenda items in ${day} are not allowed.`, 'error');
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
        showToast(t('Agenda saved successfully!'), 'success');
        const savedEventId = agendaEditingEventId;
        closeAgendaEditor();
        loadEvents(); // refresh list
        showEventDetails(savedEventId);
      } else {
        showToast(res.data?.message || t('Error saving agenda'), 'error');
      }
    }

    // ── Cancellation Management ─────────────────────
    let cancellationEventId = null;

    function openCancellationModal(eventId) {
      cancellationEventId = eventId;
      document.getElementById('cancellation-reason').value = '';
      document.getElementById('cancellation-modal').classList.add('open');
    }

    function closeCancellationModal() {
      document.getElementById('cancellation-modal').classList.remove('open');
      cancellationEventId = null;
    }

    async function submitCancellationRequest() {
      const reason = document.getElementById('cancellation-reason').value.trim();
      if (!reason) {
        showToast(document.documentElement.lang === 'ar' ? 'يرجى إدخال سبب الإلغاء' : 'Please enter a cancellation reason', 'error');
        return;
      }

      const btn = document.getElementById('confirm-cancellation-btn');
      btn.disabled = true;
      btn.textContent = document.documentElement.lang === 'ar' ? 'جاري الإرسال...' : 'Submitting...';

      try {
        const res = await api.post(`/events/${cancellationEventId}/request-cancellation`, { cancellation_reason: reason });
        if (res.ok) {
          showToast(document.documentElement.lang === 'ar' ? 'تم إرسال طلب الإلغاء بنجاح' : 'Cancellation request submitted successfully', 'success');
          closeCancellationModal();
          closeEventDetailsModal();
          loadEvents();
        } else {
          showToast(res.data?.message || 'Error', 'error');
        }
      } catch (err) {
        console.error('Error submitting cancellation:', err);
        showToast('Error', 'error');
      } finally {
        btn.disabled = false;
        btn.textContent = document.documentElement.lang === 'ar' ? 'إرسال الطلب' : 'Submit Request';
      }
    }

    async function toggleTicketSales(eventId) {
      try {
        const res = await api.patch(`/events/${eventId}/toggle-tickets`);
        if (res.ok) {
          showToast(res.data.is_tickets_open ?
            (document.documentElement.lang === 'ar' ? 'تم فتح بيع التذاكر' : 'Ticket sales are now OPEN') :
            (document.documentElement.lang === 'ar' ? 'تم إغلاق بيع التذاكر' : 'Ticket sales are now CLOSED'),
            'success'
          );
          // Refresh details to update button state
          showEventDetails(eventId);
        } else {
          showToast(res.data?.message || 'Error', 'error');
        }
      } catch (err) {
        console.error('Error toggling tickets:', err);
        showToast('Error', 'error');
      }
    }

    // ── Create Event Agenda Days Logic ─────────────────────
    function getExhibitionSchedule(dateStr) {
      const locationType = document.getElementById('e-location-type').value;
      if (locationType === 'internal') {
        const card = document.querySelector(`.int-slot-card[data-date="${dateStr}"]`);
        if (card) {
          const startEl = card.querySelector('.int-slot-start');
          const endEl = card.querySelector('.int-slot-end');
          if (startEl && endEl) return { start: startEl.value, end: endEl.value };

          const period = card.querySelector('.int-slot-period')?.value;
          const venueId = document.getElementById('e-venue').value;
          const venue = globalVenues.find(v => v.id == venueId);
          if (venue) {
            if (period === 'morning') return { start: venue.morning_start, end: venue.morning_end };
            if (period === 'evening') return { start: venue.evening_start, end: venue.evening_end };
            return { start: venue.morning_start, end: venue.evening_end };
          }
        }
      } else {
        const card = document.querySelector(`.ext-slot-card[data-date="${dateStr}"]`);
        if (card) {
          const startEl = card.querySelector('.ext-slot-start');
          const endEl = card.querySelector('.ext-slot-end');
          if (startEl && endEl) return { start: startEl.value, end: endEl.value };
        }
      }
      return { start: '09:00', end: '17:00' };
    }

    let createAgendaDays = [];

    function updateCreateAgendaDays() {
      const locationType = document.getElementById('e-location-type').value;
      let newDays = [];
      if (locationType === 'internal') {
        newDays = [...intSelectedDates].sort();
      } else {
        newDays = [...extSelectedDates].sort();
      }

      createAgendaDays = newDays;

      // Collect existing days in agenda to avoid duplicating
      const selects = document.querySelectorAll('#agenda-items-create .agenda-date');
      let existingDatesInAgenda = [];

      selects.forEach(select => {
        const currentVal = select.value;
        if (currentVal) existingDatesInAgenda.push(currentVal);

        let optionsHtml = '<option value="">' + (document.documentElement.lang === 'ar' ? 'اختر اليوم' : 'Select Day') + '</option>';
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        createAgendaDays.forEach(dStr => {
          const d = new Date(dStr + 'T00:00:00');
          const label = `${dayNames[d.getDay()]} ${d.getDate()}`;
          const isSelected = (currentVal === dStr) ? 'selected' : '';
          optionsHtml += `<option value="${dStr}" ${isSelected}>${label}</option>`;
        });
        select.innerHTML = optionsHtml;
      });

      // Remove agenda items for days that were deselected
      const agendaItems = document.querySelectorAll('#agenda-items-create .agenda-item');
      agendaItems.forEach(item => {
        const dateSelect = item.querySelector('.agenda-date');
        if (dateSelect.value && !createAgendaDays.includes(dateSelect.value)) {
          item.remove();
          existingDatesInAgenda = existingDatesInAgenda.filter(d => d !== dateSelect.value);
        }
      });

      // Auto-add missing days
      createAgendaDays.forEach(dStr => {
        if (!existingDatesInAgenda.includes(dStr)) {
          const sched = getExhibitionSchedule(dStr);
          const startTime = sched.start;
          const endTime = sched.end;

          addAgendaItem('agenda-items-create', {
            date: dStr,
            start_time: startTime,
            end_time: endTime,
            title: document.documentElement.lang === 'ar' ? 'النشاط الأول' : 'Activity 1'
          });
        }
      });
    }



    async function toggleSponsorship(eventId, checked) {
      const res = await api.patch(`/events/${eventId}/toggle-sponsorship`);
      if (res.ok) {
        showToast(res.data.event.is_sponsorship_open ? t('Sponsorship is now OPEN for this event.') : t('Sponsorship is now CLOSED for this event.'), 'success');
      } else {
        showToast(res.data?.message || t('Error updating status'), 'error');
        document.getElementById(`spon-tog-${eventId}`).checked = !checked;
      }
    }

    async function toggleExhibitorRegistration(eventId, checked) {
      const res = await api.patch(`/events/${eventId}/toggle-exhibitor-registration`);
      if (res.ok) {
        const isOpen = res.data.event.is_exhibitor_registration_open;
        const canAccept = res.data.can_accept_exhibitors;
        if (isOpen) {
          if (canAccept) {
            showToast(t('Exhibitor registration is now OPEN.'), 'success');
          } else {
            showToast(t('Registration toggled ON, but it remains inactive due to the 60-day deadline.'), 'info');
          }
        } else {
          showToast(t('Exhibitor registration is now CLOSED.'), 'success');
        }
      } else {
        showToast(res.data?.message || t('Error updating status'), 'error');
        const tog = document.getElementById(`exh-tog-${eventId}`);
        if (tog) tog.checked = !checked;
      }
    }

    async function deleteEvent(eventId) {
      if (!confirm(t('Are you sure you want to delete this event? This action cannot be undone.'))) return;
      const res = await api.delete(`/events/${eventId}`);
      if (res.ok) {
        showToast(t('Event deleted successfully'), 'success');
        loadEvents();
      } else {
        showToast(res.data?.message || t('Error deleting event'), 'error');
      }
    }

    // ── Internal Venue Multi-Day Calendar ─────────────────────────
    let intCalendarInstance = null;
    let intSelectedDates = [];

    function initIntCalendar() {
      if (intCalendarInstance) return;

      const wrap = document.getElementById('e-int-calendar-wrap');
      wrap.innerHTML = '<input id="e-int-calendar-input" type="text" style="display:none;" />';

      intCalendarInstance = initFlatpickr("#e-int-calendar-input", {
        mode: 'multiple',
        dateFormat: 'Y-m-d',
        minDate: new Date().fp_incr(60),
        inline: true,
        animate: true,
        showStats: true,
        appendTo: wrap,
        disable: [
          function (date) {
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
        onChange: function (selectedDates, dateStr) {
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
        onDayCreate: function (dObj, dStr, fp, dayElem) {
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
                tooltip.textContent = `[${t('Maintenance')}] ${maintBooking.reason}`;
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
      const eventType = document.getElementById('e-type').value;
      const isExhibition = eventType === 'معرض';

      if (intSelectedDates.length === 0) {
        container.innerHTML = '';
        return;
      }

      const format12Hr = (t24) => {
        if (!t24) return '';
        let [h, m] = t24.split(':');
        h = parseInt(h);
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        h = h ? h : 12;
        return `${h.toString().padStart(2, '0')}:${m} ${ampm}`;
      };

      const existingPeriods = {};
      container.querySelectorAll('.int-slot-card').forEach(card => {
        const date = card.dataset.date;
        const periodEl = card.querySelector('.int-slot-period');
        if (periodEl) existingPeriods[date] = periodEl.value;
      });

      const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

      container.innerHTML = intSelectedDates.map((dateStr) => {
        const d = new Date(dateStr + 'T00:00:00');
        const dayName = dayNames[d.getDay()];
        const monthName = monthNames[d.getMonth()];
        const dayNum = d.getDate();
        const venueId = document.getElementById('e-venue').value;
        const venue = globalVenues.find(v => v.id == venueId);
        const forcedPeriod = existingPeriods[dateStr] || '';

        const bookings = window.currentVenueBookings ? window.currentVenueBookings.filter(b => b.booking_date === dateStr) : [];
        const bookedPeriods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);

        const morningTime = venue ? ` \u200E(${format12Hr(venue.morning_start)} - ${format12Hr(venue.morning_end)})` : '';
        const eveningTime = venue ? ` \u200E(${format12Hr(venue.evening_start)} - ${format12Hr(venue.evening_end)})` : '';
        const fullDayTime = venue ? ` \u200E(${format12Hr(venue.morning_start)} - ${format12Hr(venue.evening_end)})` : '';

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
          <div style="flex:1; display:flex; gap:20px; align-items:flex-end; flex-wrap:wrap;">
            ${isExhibition ? `
            <input type="hidden" class="int-slot-period" value="full_day">
            <div style="flex:1.5; display:flex; gap:14px; align-items:center; min-width:260px;">
               <div style="flex:1;">
                 <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">Start</label>
                 <input type="time" class="int-slot-start form-control" value="09:00" style="padding:7px 10px;font-size:0.85rem;height:38px;" onchange="updateAgendaBoundsForDate('${dateStr}', true)" required />
               </div>
               <div style="color:#64748b;font-size:1.1rem;margin-top:18px;">→</div>
               <div style="flex:1;">
                 <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">End</label>
                 <input type="time" class="int-slot-end form-control" value="17:00" style="padding:7px 10px;font-size:0.85rem;height:38px;" required />
               </div>
            </div>
            ` : `
            <div style="flex:1; min-width:200px;">
              <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">${t('Select Period')}</label>
              <select class="int-slot-period form-control" style="padding:7px 10px;font-size:0.85rem;height:38px;" onchange="checkIntPeriodAvailability(this, '${dateStr}')" required>
                <option value="">${t('Select a period...')}</option>
                <option value="morning" ${forcedPeriod === 'morning' ? 'selected' : ''} ${bookedPeriods.includes('morning') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>${t('Morning Period')}${morningTime}</option>
                <option value="evening" ${forcedPeriod === 'evening' ? 'selected' : ''} ${bookedPeriods.includes('evening') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>${t('Evening Period')}${eveningTime}</option>
                <option value="full_day" ${forcedPeriod === 'full_day' ? 'selected' : ''} ${bookedPeriods.includes('morning') || bookedPeriods.includes('evening') || bookedPeriods.includes('full_day') ? 'disabled' : ''}>${t('Full Day')}${fullDayTime}</option>
              </select>
            </div>
            `}
          </div>
          <button type="button" onclick="removeIntDay('${dateStr}')" style="
            background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
            color:#ef4444; width:30px; height:30px; border-radius:8px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; font-size:0.9rem; margin-top:20px;
          " title="Remove this day">&times;</button>
        </div>`;
      }).join('');
      updateCreateAgendaDays();
    }


    window.removeIntDay = function (dateStr) {
      if (intCalendarInstance) {
        const current = intCalendarInstance.selectedDates;
        const dObj = new Date(dateStr + 'T00:00:00');
        const newDates = current.filter(d => d.getTime() !== dObj.getTime());
        intCalendarInstance.setDate(newDates, true);
        intSelectedDates = intSelectedDates.filter(d => d !== dateStr);
        renderIntTimeSlots();
      }
    };

    window.checkIntPeriodAvailability = function (selectElem, dateStr) {
      const bookings = window.currentVenueBookings ? window.currentVenueBookings.filter(b => b.booking_date === dateStr) : [];
      const bookedPeriods = bookings.filter(b => b.type !== 'maintenance').map(b => b.period);
      if (bookedPeriods.includes(selectElem.value) || (selectElem.value === 'full_day' && (bookedPeriods.includes('morning') || bookedPeriods.includes('evening'))) || (bookedPeriods.includes('full_day'))) {
        showToast('This period is already booked. Please select another.', 'error');
        selectElem.value = '';
      }
      updateAgendaBoundsForDate(dateStr, true);
    };

    function buildInternalSchedule() {
      const cards = document.querySelectorAll('.int-slot-card');
      const schedule = [];
      cards.forEach(card => {
        const date = card.dataset.date;
        let periodEl = card.querySelector('input.int-slot-period');
        if (!periodEl) periodEl = card.querySelector('select.int-slot-period');

        const period = periodEl ? periodEl.value : '';
        const startEl = card.querySelector('.int-slot-start');
        const endEl = card.querySelector('.int-slot-end');

        if (date && period) {
          const entry = { date, period };
          if (startEl && endEl) {
            entry.start_time = startEl.value;
            entry.end_time = endEl.value;
          }
          schedule.push(entry);
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
      wrap.innerHTML = '<input id="e-ext-calendar-input" type="text" style="display:none;" />';

      extCalendarInstance = initFlatpickr("#e-ext-calendar-input", {
        mode: 'multiple',
        dateFormat: 'Y-m-d',
        minDate: new Date().fp_incr(60),
        inline: true,
        animate: true,
        appendTo: wrap,
        onChange: function (selectedDates, dateStr) {
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
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

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
              <input type="time" class="ext-slot-start form-control" value="${prev.start}" onchange="updateAgendaBoundsForDate('${dateStr}', true)" style="padding:6px 10px;font-size:0.85rem;" />
            </div>
            <div style="color:#64748b;font-size:1.1rem;margin-top:14px;">→</div>
            <div style="flex:1;min-width:100px;">
              <label style="font-size:0.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:3px;">End</label>
              <input type="time" class="ext-slot-end form-control" value="${prev.end}" onchange="updateAgendaBoundsForDate('${dateStr}', true)" style="padding:6px 10px;font-size:0.85rem;" />
            </div>
          </div>
          <button type="button" onclick="removeExtDay('${dateStr}')" style="
            background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
            color:#ef4444; width:30px; height:30px; border-radius:8px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; font-size:0.9rem;
            transition: background 0.2s; flex-shrink:0; margin-top:10px;
          " title="Remove this day">&times;</button>
        </div>`;
      }).join('');
      updateCreateAgendaDays();
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

    function toggleCapacityInput(mode, value) {
      if (mode === 'create') {
        const wrap = document.getElementById('capacity-input-wrap');
        const input = document.getElementById('e-capacity');
        if (value === 'unlimited') {
          wrap.style.display = 'none';
          input.required = false;
        } else {
          wrap.style.display = 'block';
          input.required = true;
          input.focus();
        }
      } else if (mode === 'edit') {
        const wrap = document.getElementById('edit-capacity-input-wrap');
        const input = document.getElementById('edit-capacity');
        if (value === 'unlimited') {
          wrap.style.display = 'none';
          input.required = false;
        } else {
          wrap.style.display = 'block';
          input.required = true;
          input.focus();
        }
      }
    }

    // ── Agenda Builder ─────────────────────────────
    function addAgendaItem(containerId, data = null) {
      const container = document.getElementById(containerId);
      const item = document.createElement('div');
      item.className = 'agenda-item';
      item.style.cssText = 'display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(139,92,246,0.15);border-radius:12px;padding:12px 14px;';

      let days = [];
      if (containerId === 'agenda-items-create') days = createAgendaDays;
      else if (containerId === 'agenda-items-editor') days = agendaDays;

      const isNew = !data;
      if (days.length === 1 && !data) {
        data = { date: days[0] };
      }

      const isAr = document.documentElement.lang === 'ar';
      let optionsHtml = '<option value="">' + (isAr ? 'اختر اليوم' : 'Select Day') + '</option>';
      const dayNames = isAr ? ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      days.forEach(dStr => {
        const d = new Date(dStr + 'T00:00:00');
        const label = `${dayNames[d.getDay()]} ${d.getDate()}`;
        const selected = (data && data.date === dStr) ? 'selected' : '';
        optionsHtml += `<option value="${dStr}" ${selected}>${label}</option>`;
      });

      item.innerHTML = `
        <div style="display:flex;align-items:flex-end;gap:8px;">
          <div style="flex:1;display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
            <div style="min-width:110px;">
              <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">${t('Day')}</label>
              <select class="agenda-date form-control" required style="padding:5px 8px;font-size:0.82rem;">
                ${optionsHtml}
              </select>
            </div>
            <div style="min-width:80px;">
              <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">${t('From')}</label>
              <input type="time" class="agenda-start form-control" value="${data?.start_time || ''}" required style="padding:5px 8px;font-size:0.82rem;" />
            </div>
            <div style="padding-bottom:8px;color:#64748b;">→</div>
            <div style="min-width:80px;">
              <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">${t('To')}</label>
              <input type="time" class="agenda-end form-control" value="${data?.end_time || ''}" required style="padding:5px 8px;font-size:0.82rem;" />
            </div>
            <div style="flex:1;min-width:120px;">
              <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">${t('Activity')}</label>
              <input type="text" class="agenda-title form-control" value="${data?.title || ''}" placeholder="${t('e.g. Opening Ceremony')}" required style="padding:5px 8px;font-size:0.82rem;" />
            </div>
          </div>
          <button type="button" onclick="this.closest('.agenda-item').remove()" style="
            background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);
            color:#ef4444;width:28px;height:28px;border-radius:7px;cursor:pointer;
            display:flex;align-items:center;justify-content:center;font-size:0.85rem;
            flex-shrink:0;
          " title="${t('Remove')}">&times;</button>
        </div>
        <div style="width:100%;">
          <label style="font-size:0.6rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">${t('Description (Optional)')}</label>
          <input type="text" class="agenda-desc form-control" value="${data?.description || ''}" placeholder="${t('Brief details about this activity...')}" style="padding:5px 8px;font-size:0.82rem;" />
        </div>
      `;
      container.appendChild(item);

      // Add event listener to auto-hide if changed in the editor modal
      // Helper to update bounds for a specific agenda item
      window.updateAgendaItemBounds = function (agendaItem, forceFill = false) {
        const dateSelect = agendaItem.querySelector('.agenda-date');
        const startInput = agendaItem.querySelector('.agenda-start');
        const endInput = agendaItem.querySelector('.agenda-end');
        if (!dateSelect.value) return;

        const sched = getExhibitionSchedule(dateSelect.value);
        startInput.min = sched.start;
        startInput.max = sched.end;
        endInput.min = sched.start;
        endInput.max = sched.end;

        if (forceFill || !startInput.value) {
          startInput.value = sched.start;
        }
        if (forceFill || !endInput.value) {
          endInput.value = sched.end;
        }

        // Validation on change
        const validate = () => {
          if (startInput.value && (startInput.value < sched.start || startInput.value > sched.end)) {
            showToast(`${t('Start time must be between')} ${sched.start} ${t('and')} ${sched.end}`, 'error');
            startInput.value = sched.start;
          }
          if (endInput.value && (endInput.value < sched.start || endInput.value > sched.end)) {
            showToast(`${t('End time must be between')} ${sched.start} ${t('and')} ${sched.end}`, 'error');
            endInput.value = sched.end;
          }
        };

        startInput.onchange = validate;
        endInput.onchange = validate;
      };

      // Helper to update all agenda items for a specific date
      window.updateAgendaBoundsForDate = function (dateStr, forceFill = false) {
        document.querySelectorAll('.agenda-item').forEach(item => {
          const dateSelect = item.querySelector('.agenda-date');
          if (dateSelect && dateSelect.value === dateStr) {
            updateAgendaItemBounds(item, forceFill);
          }
        });
      };

      const selectElem = item.querySelector('.agenda-date');
      selectElem.addEventListener('change', function () {
        updateAgendaItemBounds(item, true);
        if (containerId === 'agenda-items-editor' && window.currentAgendaEditorDay) {
          if (this.value !== window.currentAgendaEditorDay) {
            item.style.display = 'none';
          }
        }
      });
      // Initial bounds set
      if (data && data.date) updateAgendaItemBounds(item, isNew);
    }



    async function loadVenues() {
      const res = await api.get('/venues');
      if (!res.ok) {
        document.getElementById('e-venue').innerHTML = '<option value="">No venues available</option>';
        return;
      }
      globalVenues = res.data;
      renderVenues(document.getElementById('e-type').value);
    }

    function renderVenues(eventType) {
      const sel = document.getElementById('e-venue');
      const isExhibition = eventType === 'معرض';

      const filtered = globalVenues.filter(v => {
        const isFairVenue = v.name.includes('معرض');
        return isExhibition ? isFairVenue : !isFairVenue;
      });

      if (filtered.length === 0) {
        sel.innerHTML = `<option value="">${document.documentElement.lang === 'ar' ? 'لا توجد قاعات متاحة لهذا النوع حالياً' : 'No halls available for this type currently'}</option>`;
      } else {
        const placeholder = isExhibition
          ? (document.documentElement.lang === 'ar' ? 'اختر المعرض...' : 'Select the exhibition...')
          : (document.documentElement.lang === 'ar' ? 'اختر القاعة...' : 'Select a hall...');

        sel.innerHTML = `<option value="">${placeholder}</option>` +
          filtered.map(v => `<option value="${v.id}">${v.name}</option>`).join('');
      }
    }

    async function updatePeriodTimes() {
      const venueId = document.getElementById('e-venue').value;
      const periodEl = document.getElementById('e-period');
      const periodSelect = periodEl ? periodEl.value : '';
      const timeLabel = document.getElementById('selected-period-time');

      if (!venueId) {
        if (timeLabel) timeLabel.textContent = document.documentElement.lang === 'ar' ? 'اختر قاعة أولاً لرؤية الوقت' : 'Select a venue first to see the time';
        window.currentVenueBookings = [];
        window.lastFetchedVenueId = null;
        if (fpInstance) fpInstance.redraw();
        // Re-init internal calendar with new bookings data
        if (intCalendarInstance) intCalendarInstance.redraw();
        checkAvailability();
        return;
      }

      const v = globalVenues.find(x => x.id == venueId);
      if (v && timeLabel) {
        const formatTime = (t24) => {
          if (!t24) return '';
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
        // Also refresh the internal multi-day calendar
        if (intCalendarInstance) {
          intCalendarInstance.redraw();
          if (intCalendarInstance.updateCustomStats) intCalendarInstance.updateCustomStats();
        }
        renderIntTimeSlots();
      }
      checkAvailability();
    }

    function checkAvailability() {
      const date = document.getElementById('e-booking-date').value;
      const periodEl = document.getElementById('e-period');

      if (!date) return;
      if (!periodEl) return; // Period dropdown no longer exists in new multi-day UI

      const periodOpts = periodEl.options;
      for (let i = 0; i < periodOpts.length; i++) {
        periodOpts[i].removeAttribute('data-booked');
        periodOpts[i].text = periodOpts[i].text.replace(' (محجوز)', '').replace(' (Booked)', '');
      }

      const bookedPeriods = (window.currentVenueBookings || []).filter(b => b.booking_date === date).map(b => b.period);

      for (let i = 0; i < periodOpts.length; i++) {
        const p = periodOpts[i].value;
        if (bookedPeriods.includes(p) || (bookedPeriods.includes('full_day')) || (p === 'full_day' && bookedPeriods.length > 0)) {
          periodOpts[i].setAttribute('data-booked', 'true');
          periodOpts[i].text += ` (${document.documentElement.lang === 'ar' ? 'محجوز' : 'Booked'})`;
        }
      }

      if (periodEl.options[periodEl.selectedIndex]?.getAttribute('data-booked') === 'true') {
        periodEl.value = '';
      }
    }

    let currentWizardStep = 1;

    function openModal() {
      currentWizardStep = 1;
      setLocationMode('internal');
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

      // Reset AI description card
      aiDescDismissed = false;
      const aiDescCard = document.getElementById('ai-desc-card');
      if (aiDescCard) { aiDescCard.style.display = 'none'; aiDescCard.style.opacity = '1'; aiDescCard.style.transform = 'translateY(0)'; }

      createAgendaDays = [];
    }

    function nextStep() {
      // Validate step 1 fields
      const title = document.getElementById('e-title').value.trim();
      const desc = document.getElementById('e-desc').value.trim();
      const type = document.getElementById('e-type').value;
      const image = document.getElementById('e-image').files[0];

      if (!title) { showToast(document.documentElement.lang === 'ar' ? 'الرجاء إدخال عنوان الحدث' : 'Please enter an event title', 'error'); document.getElementById('e-title').focus(); return; }
      if (!desc) { showToast(document.documentElement.lang === 'ar' ? 'الرجاء إدخال الوصف' : 'Please enter a description', 'error'); document.getElementById('e-desc').focus(); return; }
      if (!type) { showToast(document.documentElement.lang === 'ar' ? 'الرجاء اختيار نوع الحدث' : 'Please select an event type', 'error'); document.getElementById('e-type').focus(); return; }
      if (!image) { showToast(document.documentElement.lang === 'ar' ? 'الرجاء رفع صورة غلاف للحدث' : 'Please upload an event banner image', 'error'); document.getElementById('e-image').focus(); return; }

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
        num1.innerHTML = '&check;';
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
    document.getElementById('e-image').addEventListener('change', function (e) {
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

    // ── AI Description Generator ─────────────────────────────────
    let aiDescDismissed = false;
    let aiDescSuccessTimeout = null;
    let aiDescSuccessHideTimeout = null;
    let aiDescErrorTimeout = null;

    function checkShowAIDescCard() {
      const title = document.getElementById('e-title').value.trim();
      const desc = document.getElementById('e-desc').value.trim();
      const card = document.getElementById('ai-desc-card');

      // Only show if: title has 3+ chars, description is empty, not dismissed
      if (title.length >= 3 && !desc && !aiDescDismissed) {
        if (card.style.display !== 'block' || document.getElementById('ai-desc-prompt').style.display !== 'block') {
          card.style.display = 'block';
          card.style.animation = 'wizSlideIn 0.3s ease';
          resetAIDescState();
        }
      } else {
        const isLoading = document.getElementById('ai-desc-loading').style.display === 'block';
        if (!isLoading && (desc || title.length < 3)) {
          card.style.display = 'none';
        }
      }
    }

    // Show AI suggestion card dynamically on input
    document.getElementById('e-title').addEventListener('input', function () {
      aiDescDismissed = false;
      document.getElementById('e-desc').value = ''; // Clear old description
      checkShowAIDescCard();
    });

    // Show AI suggestion card dynamically when event type changes
    document.getElementById('e-type').addEventListener('change', function () {
      aiDescDismissed = false;
      document.getElementById('e-desc').value = ''; // Clear old description
      checkShowAIDescCard();
    });

    // Also listen for description changes (to show suggestion if cleared/deleted)
    document.getElementById('e-desc').addEventListener('input', function () {
      if (!this.value.trim()) {
        aiDescDismissed = false;
      }
      checkShowAIDescCard();
    });

    function resetAIDescState() {
      // Clear pending state-change timeouts
      if (aiDescSuccessTimeout) { clearTimeout(aiDescSuccessTimeout); aiDescSuccessTimeout = null; }
      if (aiDescSuccessHideTimeout) { clearTimeout(aiDescSuccessHideTimeout); aiDescSuccessHideTimeout = null; }
      if (aiDescErrorTimeout) { clearTimeout(aiDescErrorTimeout); aiDescErrorTimeout = null; }

      // Make sure card's opacity and transform are reset
      const card = document.getElementById('ai-desc-card');
      if (card) {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }

      document.getElementById('ai-desc-prompt').style.display = 'block';
      document.getElementById('ai-desc-loading').style.display = 'none';
      document.getElementById('ai-desc-success').style.display = 'none';
      document.getElementById('ai-desc-error').style.display = 'none';
    }

    function dismissAIDesc() {
      aiDescDismissed = true;
      const card = document.getElementById('ai-desc-card');
      card.style.opacity = '0';
      card.style.transform = 'translateY(-10px)';
      aiDescSuccessHideTimeout = setTimeout(() => {
        card.style.display = 'none';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 250);
    }

    async function generateAIDescription() {
      const title = document.getElementById('e-title').value.trim();
      if (!title) {
        showToast(document.documentElement.lang === 'ar' ? 'الرجاء إدخال عنوان الحدث أولاً' : 'Please enter an event title first', 'error');
        return;
      }

      const eventType = document.getElementById('e-type').value;

      // Switch to loading state
      document.getElementById('ai-desc-prompt').style.display = 'none';
      document.getElementById('ai-desc-loading').style.display = 'block';
      document.getElementById('ai-desc-success').style.display = 'none';
      document.getElementById('ai-desc-error').style.display = 'none';

      try {
        const res = await api.post('/events/generate-description', {
          title: title,
          event_type: eventType || null,
        });

        if (res.ok && res.data?.description) {
          // Fill description
          document.getElementById('e-desc').value = res.data.description;

          // Switch to success state
          document.getElementById('ai-desc-loading').style.display = 'none';
          document.getElementById('ai-desc-success').style.display = 'block';

          // Highlight the description textarea briefly
          const descEl = document.getElementById('e-desc');
          descEl.style.borderColor = '#10b981';
          descEl.style.boxShadow = '0 0 0 3px rgba(16,185,129,0.15)';
          setTimeout(() => {
            descEl.style.borderColor = '';
            descEl.style.boxShadow = '';
          }, 2000);

          // Auto-hide success after 3 seconds
          aiDescSuccessTimeout = setTimeout(() => {
            const card = document.getElementById('ai-desc-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(-10px)';
            aiDescSuccessHideTimeout = setTimeout(() => {
              card.style.display = 'none';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            }, 250);
          }, 3000);
        } else {
          // Show error state
          document.getElementById('ai-desc-loading').style.display = 'none';
          const errEl = document.getElementById('ai-desc-error');
          errEl.style.display = 'block';

          // Check for rate limit error
          const detail = res.data?.detail || res.data?.message || '';
          const isRateLimit = detail.toLowerCase().includes('rate limit') || detail.toLowerCase().includes('quota') || res.status === 429;

          if (isRateLimit) {
            errEl.textContent = document.documentElement.lang === 'ar' ? '⏳ تم تجاوز الحد المسموح. انتظر دقيقة وحاول مرة أخرى.' : '⏳ Rate limit reached. Please wait a minute and try again.';
          } else {
            errEl.textContent = detail || (document.documentElement.lang === 'ar' ? 'حدث خطأ أثناء توليد الوصف. حاول مرة أخرى.' : 'Failed to generate description. Please try again.');
          }
          // Show prompt again after 4 seconds
          aiDescErrorTimeout = setTimeout(() => {
            resetAIDescState();
          }, 4000);
        }
      } catch (err) {
        console.error('AI description error:', err);
        document.getElementById('ai-desc-loading').style.display = 'none';
        const errEl = document.getElementById('ai-desc-error');
        errEl.style.display = 'block';
        errEl.textContent = document.documentElement.lang === 'ar' ? 'تعذر الاتصال بخدمة الذكاء الاصطناعي.' : 'Could not connect to AI service.';
        aiDescErrorTimeout = setTimeout(() => {
          resetAIDescState();
        }, 3000);
      }
    }

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
        title: 'Event Title',
        description: 'Description',
        event_type: 'Event Type',
        capacity: 'Capacity',
        event_objective: 'Event Objective',
        target_audience: 'Target Audience',
        image: 'Event Banner',
        competent_authority_approval: 'Competent Authority Approval',
        booking_proof: 'Booking Proof'
      };

      fields.forEach(f => {
        switch (f) {
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
                <option value="مؤتمر" ${ev.event_type === 'مؤتمر' ? 'selected' : ''}>Conference</option>
                <option value="ندوة" ${ev.event_type === 'ندوة' ? 'selected' : ''}>Seminar</option>
                <option value="ورشة عمل" ${ev.event_type === 'ورشة عمل' ? 'selected' : ''}>Workshop</option>
                <option value="دورة تدريبية" ${ev.event_type === 'دورة تدريبية' ? 'selected' : ''}>Training</option>
                <option value="ترفيه" ${ev.event_type === 'ترفيه' ? 'selected' : ''}>Entertainment</option>
                <option value="ملتقى علمي" ${ev.event_type === 'ملتقى علمي' ? 'selected' : ''}>Scientific Forum</option>
                <option value="رياضة" ${ev.event_type === 'رياضة' ? 'selected' : ''}>Sports</option>
                <option value="تقنية" ${ev.event_type === 'تقنية' ? 'selected' : ''}>Technology</option>
                <option value="اجتماعية" ${ev.event_type === 'اجتماعية' ? 'selected' : ''}>Social</option>
                <option value="معرض" ${ev.event_type === 'معرض' ? 'selected' : ''}>Exhibition</option>
              </select></div>`;
            break;
          case 'capacity':
            const isUnlimited = ev.capacity === null;
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <div style="display:flex; gap:15px; margin-bottom:10px; background:rgba(255,255,255,0.03); padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.05);">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:#fff;">
                  <input type="radio" name="edit_capacity_type" value="fixed" ${!isUnlimited ? 'checked' : ''} onchange="toggleCapacityInput('edit', this.value)" style="width:16px; height:16px; accent-color:#8b5cf6;"> Fixed Number
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:#fff;">
                  <input type="radio" name="edit_capacity_type" value="unlimited" ${isUnlimited ? 'checked' : ''} onchange="toggleCapacityInput('edit', this.value)" style="width:16px; height:16px; accent-color:#8b5cf6;"> Unlimited (مفتوح)
                </label>
              </div>
              <div id="edit-capacity-input-wrap" style="${isUnlimited ? 'display:none;' : ''}">
                <input id="edit-capacity" type="number" class="form-control" value="${ev.capacity || ''}" min="1" ${!isUnlimited ? 'required' : ''} />
              </div></div>`;
            break;
          case 'event_objective':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <textarea id="edit-objective" class="form-control" rows="2" required>${ev.event_objective || ''}</textarea></div>`;
            break;
          case 'target_audience':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-audience" type="text" class="form-control" value="${ev.target_audience || ''}" required /></div>`;
            break;
          case 'image':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-image" type="file" accept="image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.image ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <a href="/storage/${ev.image}" target="_blank" style="color:#8b5cf6;">View ↗</a></small>` : ''}</div>`;
            break;
          case 'ministry_document':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-ministry" type="file" accept=".pdf,image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.ministry_document_path ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <button onclick="downloadEventDoc(${ev.id}, 'ministry_document')" style="color:#8b5cf6;background:none;border:none;padding:0;font:inherit;cursor:pointer;text-decoration:underline;">View ↗</button></small>` : ''}</div>`;
            break;
          case 'booking_proof':
            html += `<div class="form-group"><label class="form-label">${fieldLabels[f]}</label>
              <input id="edit-proof" type="file" accept=".pdf,image/*" class="form-control" style="padding:7px 10px;" />
              ${ev.booking_proof_path ? `<small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Current: <button onclick="downloadEventDoc(${ev.id}, 'booking_proof')" style="color:#8b5cf6;background:none;border:none;padding:0;font:inherit;cursor:pointer;text-decoration:underline;">View ↗</button></small>` : ''}</div>`;
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

      const capType = document.querySelector('input[name="edit_capacity_type"]:checked')?.value;
      const capEl = document.getElementById('edit-capacity');
      if (capEl) {
        formData.append('capacity', capType === 'unlimited' ? '' : capEl.value);
      }

      const objEl = document.getElementById('edit-objective');
      if (objEl) formData.append('event_objective', objEl.value);

      const audEl = document.getElementById('edit-audience');
      if (audEl) formData.append('target_audience', audEl.value);

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

      const isAr = document.documentElement.lang === 'ar';

      if (mode === 'internal') {
        indicator.style.transform = isAr ? 'translateX(100%)' : 'translateX(0)';
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
        indicator.style.transform = isAr ? 'translateX(0)' : 'translateX(100%)';
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

    // Exhibition Hint Toggle Logic
    const eTypeEl = document.getElementById('e-type');
    if (eTypeEl) {
      eTypeEl.addEventListener('change', function () {
        const hint = document.getElementById('exhibition-hint');
        if (this.value === 'معرض') {
          hint.style.display = 'block';
        } else {
          hint.style.display = 'none';
        }
        renderVenues(this.value);
        renderIntTimeSlots();
      });
    }

    // Global modal functions
    window.openModal = window.openModal || function () {
      const modal = document.getElementById('event-modal');
      if (modal) {
        modal.classList.add('open');
        // Reset everything
        const form = document.getElementById('event-form');
        form.reset();
        document.getElementById('exhibition-hint').style.display = 'none';
        renderVenues(document.getElementById('e-type').value);
        goToStep(1);
      }
    };

    window.closeModal = window.closeModal || function () {
      const modal = document.getElementById('event-modal');
      if (modal) modal.classList.remove('open');
    };

    // Delegated listener for Edit Modal type change
    document.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'edit-type') {
        // Handle edit modal logic if needed
      }
    });

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

        if (intSelectedDates.length === 0) {
          showToast('Please select at least one day for the venue.', 'error');
          return;
        }
        if (internalSchedule.length < intSelectedDates.length) {
          showToast('Please select a period for all selected days.', 'error');
          return;
        }
        formData.append('internal_schedule', JSON.stringify(internalSchedule));
      } else {
        formData.append('external_venue_name', document.getElementById('e-ext-name').value);
        formData.append('external_venue_location', document.getElementById('e-ext-location').value);

        // Build external_schedule JSON from time slots
        const schedule = buildExternalSchedule();
        if (extSelectedDates.length === 0) {
          showToast('Please select at least one day for the external venue.', 'error');
          return;
        }
        if (schedule.length < extSelectedDates.length) {
          showToast('Please ensure start and end times are set for all external days.', 'error');
          return;
        }
        formData.append('external_schedule', JSON.stringify(schedule));

        const proofFile = document.getElementById('e-booking-proof').files[0];
        if (proofFile) {
          formData.append('booking_proof', proofFile);
        }
      }

      const capType = document.querySelector('input[name="capacity_type"]:checked').value;
      const capValue = document.getElementById('e-capacity').value;
      formData.append('capacity', capType === 'unlimited' ? '' : capValue);
      formData.append('event_objective', document.getElementById('e-objective').value);
      formData.append('target_audience', document.getElementById('e-audience').value);

      const imageFile = document.getElementById('e-image').files[0];
      if (imageFile) {
        formData.append('image', imageFile);
      }

      // Ministry document (required for all events)
      const ministryFile = document.getElementById('e-ministry-doc').files[0];
      if (ministryFile) {
        formData.append('ministry_document', ministryFile);
      }

      // Agenda (Required) - extract from flat list
      if (createAgendaDays.length === 0) {
        showToast(document.documentElement.lang === 'ar' ? 'يرجى تحديد يوم واحد على الأقل.' : 'Please select at least one day.', 'error');
        return;
      }

      let agendaObj = {};
      const items = document.querySelectorAll('#agenda-items-create .agenda-item');
      items.forEach(item => {
        const date = item.querySelector('.agenda-date').value;
        const title = item.querySelector('.agenda-title').value.trim();
        const startTime = item.querySelector('.agenda-start').value;
        const endTime = item.querySelector('.agenda-end').value;
        const description = item.querySelector('.agenda-desc').value.trim();

        if (date && title && startTime && endTime) {
          if (!agendaObj[date]) agendaObj[date] = [];
          agendaObj[date].push({ title, start_time: startTime, end_time: endTime, description });
        }
      });

      let missingAgendaDay = false;
      for (const day of createAgendaDays) {
        if (!agendaObj[day] || agendaObj[day].length === 0) {
          missingAgendaDay = true;
          break;
        }
      }

      if (missingAgendaDay) {
        showToast(document.documentElement.lang === 'ar' ? 'الاجندة مطلوبة لكل يوم. يرجى اضافة عنصر واحد على الاقل لكل يوم.' : 'Agenda is required for each day. Please add at least one agenda item for every day.', 'error');
        return;
      }

      let isValidAgenda = true;
      const getFormatTime = (t24) => { if (!t24) return '00:00'; return t24.substring(0, 5); };

      for (const day of Object.keys(agendaObj)) {
        let startBound = "00:00", endBound = "23:59";

        if (locationType === 'external') {
          const schedule = buildExternalSchedule();
          const s = schedule.find(x => x.date === day);
          if (s) { startBound = s.start_time; endBound = s.end_time; }
        } else {
          const schedule = buildInternalSchedule();
          const s = schedule.find(x => x.date === day);
          const venueId = document.getElementById('e-venue').value;
          const v = globalVenues.find(x => x.id == venueId);
          if (s && v) {
            if (s.period === 'morning') {
              startBound = getFormatTime(v.morning_start); endBound = getFormatTime(v.morning_end);
            } else if (s.period === 'evening') {
              startBound = getFormatTime(v.evening_start); endBound = getFormatTime(v.evening_end);
            } else {
              startBound = getFormatTime(v.morning_start); endBound = getFormatTime(v.evening_end);
            }
          }
        }

        const items = agendaObj[day] || [];
        items.sort((a, b) => a.start_time.localeCompare(b.start_time));

        for (let i = 0; i < items.length; i++) {
          const item = items[i];
          if (item.start_time >= item.end_time) {
            showToast(`Invalid time in ${day}. Start time must be before end time.`, 'error');
            isValidAgenda = false;
            break;
          }
          if (item.start_time < startBound || item.end_time > endBound) {
            showToast(`Invalid time in ${day}. Agenda must be strictly between event hours (${startBound} and ${endBound}).`, 'error');
            isValidAgenda = false;
            break;
          }
          if (i > 0 && item.start_time < items[i - 1].end_time) {
            showToast(`عناصر جدول الأعمال المتداخلة في ${day} غير مسموحة.`, 'error');
            isValidAgenda = false;
            break;
          }
        }
        if (!isValidAgenda) break;
      }

      if (!isValidAgenda) return;
      formData.append('agenda', JSON.stringify(agendaObj));

      const res = await api.postForm('/events', formData);

      if (res.ok) { showToast('Event submitted for approval!', 'success'); closeModal(); loadEvents(); }
      else {
        const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
        showToast(msg, 'error');
      }
    });

    async function downloadEventDoc(eventId, type) {
      const token = sessionStorage.getItem('token');
      showToast('Downloading document...', 'info');
      try {
        const res = await fetch(`/api/events/${eventId}/download-document/${type}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!res.ok) {
          showToast('Download failed.', 'error');
          return;
        }

        let filename = `${type}_${eventId}.pdf`;
        const disposition = res.headers.get('content-disposition');
        if (disposition && disposition.indexOf('filename=') !== -1) {
          const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
          const matches = filenameRegex.exec(disposition);
          if (matches != null && matches[1]) {
            filename = matches[1].replace(/['"]/g, '');
          }
        }

        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
        showToast('Download complete', 'success');
      } catch (err) {
        showToast('Error downloading document', 'error');
      }
    }

    // ══════════════════════════════════════════════════════════════
    //  AI Attendance Prediction
    // ══════════════════════════════════════════════════════════════
    let aiPredictionTimer = null;

    /**
     * Determine if the selected schedule includes a weekend day (Friday or Saturday — Libya).
     */
    function selectedDaysIncludeWeekend() {
      const locationType = document.getElementById('e-location-type').value;
      const dates = locationType === 'internal' ? intSelectedDates : extSelectedDates;
      for (const dateStr of dates) {
        const d = new Date(dateStr + 'T00:00:00');
        const day = d.getDay(); // 0=Sun, 5=Fri, 6=Sat
        if (day === 5 || day === 6) return 1;
      }
      return 0;
    }

    /**
     * Determine the dominant time period from the schedule (Morning or Evening).
     */
    function getTimePeriod() {
      const locationType = document.getElementById('e-location-type').value;
      if (locationType === 'internal') {
        const slots = document.querySelectorAll('.int-slot-card');
        let eveningCount = 0;
        slots.forEach(card => {
          const periodEl = card.querySelector('.int-slot-period') || card.querySelector('select.int-slot-period');
          if (periodEl && periodEl.value === 'evening') eveningCount++;
        });
        return eveningCount > slots.length / 2 ? 'Evening' : 'Morning';
      } else {
        // External — check if average start time is after 15:00 (3 PM)
        const slots = document.querySelectorAll('.ext-slot-card');
        let eveningCount = 0;
        slots.forEach(card => {
          const startEl = card.querySelector('.ext-slot-start');
          if (startEl && startEl.value >= '15:00') eveningCount++;
        });
        return eveningCount > slots.length / 2 ? 'Evening' : 'Morning';
      }
    }

    /**
     * Trigger the AI prediction (debounced — waits 800ms after last change).
     */
    function triggerAIPrediction() {
      if (aiPredictionTimer) clearTimeout(aiPredictionTimer);
      aiPredictionTimer = setTimeout(fetchAIPrediction, 800);
    }

    async function fetchAIPrediction() {
      const eventType = document.getElementById('e-type').value;
      const locationType = document.getElementById('e-location-type').value;
      const dates = locationType === 'internal' ? intSelectedDates : extSelectedDates;

      // Need: event type + at least 1 day selected
      if (!eventType || dates.length === 0) {
        document.getElementById('ai-prediction-card').style.display = 'none';
        return;
      }

      const card = document.getElementById('ai-prediction-card');
      const loadingEl = document.getElementById('ai-prediction-loading');
      const resultEl = document.getElementById('ai-prediction-result');
      const errorEl = document.getElementById('ai-prediction-error');

      // Show card with loading state
      card.style.display = 'block';
      loadingEl.style.display = 'block';
      resultEl.style.display = 'none';
      errorEl.style.display = 'none';

      const payload = {
        event_type: eventType,
        total_days: dates.length,
        includes_weekend: selectedDaysIncludeWeekend(),
        time_period: getTimePeriod(),
      };

      try {
        const res = await api.post('/events/predict-attendance', payload);
        loadingEl.style.display = 'none';

        if (res.ok && res.data.status === 'success') {
          const predicted = res.data.predicted_attendance;
          const lower = res.data.predicted_lower || predicted;
          const upper = res.data.predicted_upper || predicted;

          // Show point estimate with range
          const rangeText = (lower !== upper && lower > 0 && upper > 0)
            ? ` (${lower.toLocaleString()} – ${upper.toLocaleString()})`
            : '';
          document.getElementById('ai-predicted-number').textContent = predicted.toLocaleString() + rangeText;

          // Hint: recommend setting capacity based on prediction range
          const isAr = document.documentElement.lang === 'ar';
          const hint = isAr
            ? `بناءً على بيانات الفعاليات السابقة، ننصحك بتحديد السعة بحوالي ${upper.toLocaleString()} شخص أو أكثر لتغطية الطلب المتوقع.`
            : `Based on historical data, we recommend setting the capacity to around ${upper.toLocaleString()} or more to cover expected demand.`;
          document.getElementById('ai-prediction-hint').textContent = hint;
          resultEl.style.display = 'block';
        } else {
          errorEl.textContent = res.data?.message || (document.documentElement.lang === 'ar' ? 'تعذر الحصول على التوقع' : 'Could not get prediction');
          errorEl.style.display = 'block';
        }
      } catch (err) {
        loadingEl.style.display = 'none';
        errorEl.textContent = document.documentElement.lang === 'ar' ? 'خدمة الذكاء الاصطناعي غير متاحة حالياً' : 'AI service is currently unavailable';
        errorEl.style.display = 'block';
      }
    }

    // ── Wire up AI prediction triggers ──
    // Trigger on event type change
    document.getElementById('e-type').addEventListener('change', triggerAIPrediction);

    // Patch the calendar onChange handlers to also trigger AI prediction
    const _origRenderIntTimeSlots = renderIntTimeSlots;
    renderIntTimeSlots = function () {
      _origRenderIntTimeSlots();
      triggerAIPrediction();
    };
    const _origRenderExtTimeSlots = renderExtTimeSlots;
    renderExtTimeSlots = function () {
      _origRenderExtTimeSlots();
      triggerAIPrediction();
    };
  </script>

  <!-- Unpublish Confirmation Modal -->
  <div class="modal-overlay" id="unpublish-confirm-modal" style="z-index: 1100;">
    <div class="modal" style="max-width: 400px; text-align: center; padding: 30px 20px;">
      <div style="margin-bottom: 16px; display: flex; justify-content: center;">
        <svg width="48" height="48" fill="none" stroke="#f59e0b" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h3 style="margin: 0 0 10px; color: #fff; font-size: 1.2rem;"><script>document.write(t('Unpublish'))</script></h3>
      <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 24px;"><script>document.write(t('Are you sure you want to unpublish this event? It will no longer be visible to the public.'))</script></p>
      <div style="display: flex; gap: 12px; justify-content: center;">
        <button class="btn btn-ghost btn-sm" onclick="closeUnpublishConfirmModal()"><script>document.write(t('Cancel'))</script></button>
        <button class="btn btn-sm" id="unpublish-confirm-btn" style="background: #ef4444; border-color: #ef4444; color: #fff;"><script>document.write(t('Confirm'))</script></button>
      </div>
    </div>
  </div>

  <!-- Expand Capacity Modal -->
  <div class="modal-overlay" id="expand-capacity-modal">
    <div class="modal" style="max-width:400px; padding:20px; text-align:center;">
      <h3 class="modal-title" style="margin-bottom:15px;"><script>document.write(t('Edit Event Capacity'))</script></h3>
      <div style="margin-bottom: 20px; color: var(--text-muted); font-size: 0.9rem; text-align: start;">
        <div><script>document.write(t('Current capacity:'))</script> <strong id="expand-modal-current"></strong></div>
        <div><script>document.write(t('Venue max capacity:'))</script> <strong id="expand-modal-max"></strong></div>
      </div>
      <div class="form-group text-start" style="text-align: start;">
        <label class="form-label"><script>document.write(t('New total capacity'))</script></label>
        <input type="number" id="expand-capacity-input" class="form-control" style="text-align:center;" min="1">
      </div>
      <input type="hidden" id="expand-capacity-event-id">
      <input type="hidden" id="expand-capacity-venue-max">
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button class="btn btn-ghost" onclick="closeExpandCapacityModal()"><script>document.write(t('Cancel'))</script></button>
        <button class="btn btn-primary" onclick="submitExpandCapacity()"><script>document.write(t('Save'))</script></button>
      </div>
    </div>
  </div>

  <!-- Profile Details Modal -->
  <div class="modal-overlay" id="profile-details-modal">
    <div class="modal"
      style="max-width:500px; width:95%; padding:0; border-top:3.5px solid var(--accent2); max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
      <div
        style="padding:16px 20px 12px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="modal-title" style="margin:0;font-size:1.1rem;display:flex;align-items:center;gap:8px;"><svg
            width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            style="display:block;">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
          <script>document.write(t('Public Profile'))</script>
        </h3>
        <button class="modal-close" onclick="closeProfileModal()">&times;</button>
      </div>
      <div id="profile-details-content"
        style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:16px;">
        <div class="spinner" style="margin:40px auto"></div>
      </div>
    </div>
  </div>

  <!-- Event Details Modal -->
  <div class="modal-overlay" id="event-details-modal">
    <div class="modal ed-modal">
      <button class="ed-close-btn" onclick="closeEventDetailsModal()">&times;</button>
      <div id="event-details-content" class="ed-content"></div>
    </div>
  </div>

  <!-- Agenda Editor Modal -->
  <div class="modal-overlay" id="agenda-editor-modal">
    <div class="modal" style="max-width:600px; max-height: 88vh; overflow-y: auto; margin: 20px 0; padding-top: 0;">
      <div class="modal-header"
        style="position: sticky; top: 0; background: rgba(15,18,25,0.97); backdrop-filter: blur(16px); z-index: 10; padding: 24px 0 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="modal-title"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle; margin-inline-end:6px;">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <script>document.write(t('Edit Event Agenda'))</script>
        </h3>
        <button class="modal-close" onclick="closeAgendaEditor()">&times;</button>
      </div>
      <div style="padding: 16px 0;">
        <!-- Agenda Tabs for selected day -->
        <div id="agenda-editor-tabs"
          style="display:flex;gap:8px;overflow-x:auto;padding-bottom:10px;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.06);scrollbar-width:thin;">
        </div>

        <!-- Agenda items -->
        <div id="agenda-items-editor" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;"></div>
        <button type="button" class="btn btn-ghost btn-sm"
          onclick="addAgendaItem('agenda-items-editor', {date: window.currentAgendaEditorDay || ''})"
          style="display:flex;align-items:center;gap:6px;margin-bottom:16px;">
          <span style="font-size:1.1rem;">+</span>
          <script>document.write(t('Add Agenda Item to Selected Day'))</script>
        </button>
        <div
          style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid rgba(255,255,255,0.06);padding-top:14px;">
          <button class="btn btn-ghost" onclick="closeAgendaEditor()">
            <script>document.write(t('Cancel'))</script>
          </button>
          <button class="btn btn-primary" onclick="saveAgenda()" style="display:flex;align-items:center;gap:6px;"><svg
              width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              style="display:block;">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <script>document.write(t('Save Agenda'))</script>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cancellation Reason Modal -->
  <div class="modal-overlay" id="cancellation-modal">
    <div class="modal" style="max-width:450px;">
      <div class="modal-header">
        <h3 class="modal-title"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24"
            style="display:inline-block; vertical-align:middle; margin-inline-end:6px; color:var(--warning);">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg> Request Event Cancellation</h3>
        <button class="modal-close" onclick="closeCancellationModal()">&times;</button>
      </div>
      <div class="modal-body">
        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:16px;">
          Please provide a reason for cancelling this event. This request will be sent to the administrator for
          approval.
          <br><br>
          <strong style="color:var(--danger)">Note:</strong> Ticket sales will be suspended immediately upon submitting
          this request.
        </p>
        <div class="form-group">
          <label class="form-label">Cancellation Reason</label>
          <textarea id="cancellation-reason" class="form-control" rows="4"
            placeholder="e.g. Unforeseen circumstances, medical emergency..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeCancellationModal()">Cancel</button>
        <button class="btn btn-danger" id="confirm-cancellation-btn" onclick="submitCancellationRequest()">Submit
          Request</button>
      </div>
    </div>
  </div>
  <style>
    /* ═══════════════════════════════════════════════════════════════════════
       Wizard Step Animations
       ═══════════════════════════════════════════════════════════════════════ */
    @keyframes wizSlideIn {
      from {
        opacity: 0;
        transform: translateX(20px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* ═══════════════════════════════════════════════════════════════════════
       Flatpickr Premium Calendar — Event Manager Booking View
       ═══════════════════════════════════════════════════════════════════════ */

    .flatpickr-calendar {
      background: #0f1219 !important;
      border: 1px solid rgba(139, 92, 246, 0.15) !important;
      border-radius: 20px !important;
      padding: 28px !important;
      font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
      height: auto !important;
      max-height: none !important;
      overflow: visible !important;
    }

    /* Only popup (non-inline) calendars get the dark overlay + fixed centering */
    .flatpickr-calendar:not(.inline) {
      box-shadow:
        0 0 0 100vmax rgba(0, 0, 0, 0.65),
        0 24px 60px rgba(0, 0, 0, 0.8),
        0 0 40px rgba(139, 92, 246, 0.06) !important;
      width: 500px !important;
    }

    .flatpickr-calendar.open:not(.inline) {
      position: fixed !important;
      top: 50% !important;
      left: 50% !important;
      transform: translate(-50%, -50%) !important;
      right: auto !important;
      bottom: auto !important;
      z-index: 999999 !important;
      margin: 0 !important;
      animation: calendarFadeIn 0.25s ease-out !important;
    }

    /* Inline calendars (external venue) - flow naturally inside their container */
    .flatpickr-calendar.inline {
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
      width: 100% !important;
      max-width: 100% !important;
      position: relative !important;
    }

    @keyframes calendarFadeIn {
      from {
        opacity: 0;
        transform: translate(-50%, -48%) scale(0.97);
      }

      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    /* ── Month Navigation ── */
    .flatpickr-months {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      margin-bottom: 16px !important;
      position: relative !important;
      height: 40px !important;
      background: transparent !important;
      border: none !important;
      padding: 0 !important;
    }

    .flatpickr-months .flatpickr-month {
      background: transparent !important;
      border: none !important;
      height: 40px !important;
      overflow: visible !important;
    }

    .flatpickr-current-month {
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 6px !important;
      font-size: 1.15rem !important;
      font-weight: 700 !important;
      color: #f1f5f9 !important;
      padding: 0 !important;
      height: 100% !important;
      letter-spacing: -0.01em !important;
      background: transparent !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
      appearance: none !important;
      -webkit-appearance: none !important;
      background: transparent !important;
      border: none !important;
      color: #f1f5f9 !important;
      font-weight: 700 !important;
      font-size: 1.15rem !important;
      cursor: pointer !important;
      padding: 0 2px !important;
      margin: 0 !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
      background: transparent !important;
    }

    .flatpickr-current-month .numInputWrapper {
      width: 5ch !important;
      background: transparent !important;
    }

    .flatpickr-current-month .numInputWrapper span {
      display: none !important;
    }

    .flatpickr-current-month .numInputWrapper input.cur-year {
      font-weight: 700 !important;
      color: #f1f5f9 !important;
      font-size: 1.15rem !important;
      padding: 0 !important;
      margin: 0 !important;
      background: transparent !important;
    }

    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
      position: absolute !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 34px !important;
      height: 34px !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0 !important;
      fill: #64748b !important;
      color: #64748b !important;
      transition: all 0.2s ease !important;
      background: transparent !important;
    }

    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg {
      width: 16px !important;
      height: 16px !important;
    }

    html[lang="ar"] .flatpickr-months .flatpickr-prev-month {
      right: 0 !important;
      left: auto !important;
    }

    html[lang="ar"] .flatpickr-months .flatpickr-next-month {
      left: 0 !important;
      right: auto !important;
    }

    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
      background: rgba(139, 92, 246, 0.1) !important;
      fill: #c4b5fd !important;
      color: #c4b5fd !important;
    }

    /* ── Grid Layout ── */
    .flatpickr-innerContainer,
    .flatpickr-rContainer,
    .dayContainer,
    .flatpickr-days {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 100% !important;
      overflow: visible !important;
    }

    .flatpickr-weekdays {
      display: grid !important;
      grid-template-columns: repeat(7, 1fr) !important;
      margin-bottom: 8px !important;
      width: 100% !important;
      height: auto !important;
      padding-bottom: 8px !important;
      border-bottom: none !important;
      background: transparent !important;
    }

    .flatpickr-weekdaycontainer {
      display: contents !important;
      background: transparent !important;
    }

    span.flatpickr-weekday {
      color: #64748b !important;
      font-size: 0.72rem !important;
      font-weight: 600 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      letter-spacing: 0.05em !important;
      background: transparent !important;
    }

    .dayContainer {
      display: grid !important;
      grid-template-columns: repeat(7, 1fr) !important;
      gap: 6px !important;
      justify-items: center !important;
    }

    /* ── Day Cells ── */
    .flatpickr-day {
      width: 100% !important;
      max-width: 100% !important;
      height: 44px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      background: rgba(255, 255, 255, 0.025) !important;
      border: 1px solid rgba(255, 255, 255, 0.06) !important;
      border-radius: 10px !important;
      color: #cbd5e1 !important;
      font-size: 0.9rem !important;
      font-weight: 600 !important;
      transition: all 0.15s ease !important;
      position: relative !important;
      margin: 0 !important;
      line-height: 1 !important;
      cursor: pointer !important;
    }

    .flatpickr-day:hover,
    .flatpickr-day:focus {
      background: rgba(139, 92, 246, 0.08) !important;
      border-color: rgba(139, 92, 246, 0.25) !important;
      color: #f1f5f9 !important;
      z-index: 2 !important;
      transform: scale(1.04);
    }

    /* Today — purple accent */
    .flatpickr-day.today {
      border: 2px solid #8b5cf6 !important;
      background: rgba(139, 92, 246, 0.06) !important;
      color: #c4b5fd !important;
    }

    /* Selected — solid purple gradient */
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
      background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
      border-color: #8b5cf6 !important;
      color: #fff !important;
      z-index: 5 !important;
      box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4) !important;
      transform: scale(1.04);
    }

    /* Past / Other month */
    .flatpickr-day.prevMonthDay,
    .flatpickr-day.nextMonthDay {
      opacity: 0.15 !important;
      background: transparent !important;
      border-color: transparent !important;
      cursor: default !important;
    }

    .flatpickr-day.prevMonthDay:hover,
    .flatpickr-day.nextMonthDay:hover {
      transform: none !important;
    }

    /* ── Booking States ── */

    /* Fully Booked — red tint */
    .flatpickr-day.date-fully-booked {
      background: rgba(239, 68, 68, 0.18) !important;
      border-color: rgba(239, 68, 68, 0.5) !important;
      color: #fca5a5 !important;
      cursor: not-allowed !important;
    }

    .flatpickr-day.date-fully-booked:hover {
      background: rgba(239, 68, 68, 0.25) !important;
      transform: none !important;
    }

    /* Partially Booked — amber tint */
    .flatpickr-day.date-partially-booked {
      background: rgba(245, 158, 11, 0.15) !important;
      border-color: rgba(245, 158, 11, 0.45) !important;
      color: #fbbf24 !important;
    }

    .flatpickr-day.date-partially-booked:hover {
      background: rgba(245, 158, 11, 0.22) !important;
    }

    /* ── Maintenance ── */
    .flatpickr-day.date-maintenance {
      background:
        repeating-linear-gradient(-45deg,
          transparent,
          transparent 3px,
          rgba(245, 158, 11, 0.12) 3px,
          rgba(245, 158, 11, 0.12) 6px),
        rgba(245, 158, 11, 0.06) !important;
      border: 1.5px solid rgba(245, 158, 11, 0.45) !important;
      color: #fbbf24 !important;
      cursor: not-allowed !important;
      position: relative !important;
    }

    .flatpickr-day.date-maintenance:hover {
      background:
        repeating-linear-gradient(-45deg,
          transparent,
          transparent 3px,
          rgba(245, 158, 11, 0.18) 3px,
          rgba(245, 158, 11, 0.18) 6px),
        rgba(245, 158, 11, 0.1) !important;
      transform: none !important;
    }

    .flatpickr-day.date-maintenance::after {
      content: '\\1F527';
      position: absolute;
      bottom: 1px;
      right: 3px;
      font-size: 10px;
      line-height: 1;
      opacity: 0.75;
    }

    /* ── Maintenance Reason Tooltip ── */
    .flatpickr-day .maint-tooltip {
      display: none;
      position: absolute;
      bottom: calc(100% + 10px);
      left: 50%;
      transform: translateX(-50%);
      background: #1a1e2e;
      border: 1px solid rgba(245, 158, 11, 0.35);
      border-radius: 10px;
      padding: 8px 14px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #fbbf24;
      white-space: nowrap;
      z-index: 99999;
      pointer-events: none;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
      max-width: 220px;
      overflow: hidden;
      text-overflow: ellipsis;
      animation: tooltipFadeIn 0.15s ease-out;
    }

    @keyframes tooltipFadeIn {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(4px);
      }

      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
    }

    .flatpickr-day .maint-tooltip::after {
      content: '';
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      border: 6px solid transparent;
      border-top-color: rgba(245, 158, 11, 0.35);
    }

    .flatpickr-day:hover .maint-tooltip {
      display: block;
    }

    /* ── Misc ── */
    .flatpickr-calendar.arrowTop:before,
    .flatpickr-calendar.arrowTop:after {
      display: none !important;
    }

    html[lang="ar"] .flatpickr-calendar {
      direction: rtl;
    }

    /* ── Form Sections ───────────────────────────── */
    .form-section {
      background: rgba(255, 255, 255, 0.015);
      border: 1px solid rgba(255, 255, 255, 0.04);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
    }

    .form-section-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── Location Cards ───────────────────────────── */
    .location-cards {
      display: flex;
      gap: 12px;
    }

    .loc-card {
      flex: 1;
      cursor: pointer;
    }

    .loc-card input {
      display: none;
    }

    .loc-card-content {
      display: flex;
      align-items: center;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.015);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
    }

    .loc-icon {
      font-size: 22px;
      margin-right: 14px;
      background: rgba(255, 255, 255, 0.05);
      padding: 8px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .loc-details {
      flex: 1;
    }

    .loc-title {
      font-weight: 700;
      color: #fff;
      font-size: 0.9rem;
      margin-bottom: 2px;
    }

    .loc-desc {
      font-size: 0.7rem;
      color: var(--text-muted);
      line-height: 1.3;
    }

    .loc-radio {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 12px;
    }

    .loc-radio-inner {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: transparent;
      transition: all 0.2s;
    }

    .loc-card:hover .loc-card-content {
      background: rgba(255, 255, 255, 0.04);
      border-color: rgba(255, 255, 255, 0.15);
    }

    /* Internal Theme */
    .loc-internal .loc-icon {
      background: color-mix(in srgb, var(--primary) 15%, transparent);
      text-shadow: 0 0 10px rgba(110, 64, 242, 0.4);
    }

    .loc-internal input:checked+.loc-card-content {
      border-color: var(--primary);
      background: color-mix(in srgb, var(--primary) 6%, transparent);
      box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 10%, transparent);
    }

    .loc-internal input:checked+.loc-card-content .loc-radio {
      border-color: var(--primary);
    }

    .loc-internal input:checked+.loc-card-content .loc-radio-inner {
      background: var(--primary);
    }

    /* External Theme */
    .loc-external .loc-icon {
      background: color-mix(in srgb, #22d3ee 15%, transparent);
      text-shadow: 0 0 10px rgba(34, 211, 238, 0.4);
    }

    .loc-external input:checked+.loc-card-content {
      border-color: #22d3ee;
      background: color-mix(in srgb, #22d3ee 6%, transparent);
      box-shadow: 0 4px 12px color-mix(in srgb, #22d3ee 10%, transparent);
    }

    .loc-external input:checked+.loc-card-content .loc-radio {
      border-color: #22d3ee;
    }

    .loc-external input:checked+.loc-card-content .loc-radio-inner {
      background: #22d3ee;
    }

    /* ── Period Cards ───────────────────────────── */

    /* ── Period Cards ───────────────────────────── */
    .period-cards {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .period-card {
      flex: 1;
      cursor: pointer;
    }

    .period-card input {
      display: none;
    }

    .period-card-content {
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
      border-radius: 8px;
      padding: 8px 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s;
    }

    .period-card:hover .period-card-content {
      background: rgba(255, 255, 255, 0.05);
    }

    .period-card input:checked+.period-card-content {
      border-color: var(--primary);
      background: color-mix(in srgb, var(--primary) 10%, transparent);
    }

    .period-title {
      font-weight: 600;
      color: #fff;
      font-size: 0.85rem;
    }

    .period-time {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    /* ── Expand Capacity Modal (above event details) ──── */
    #expand-capacity-modal {
      z-index: 1001;
    }

    /* ── Event Details Modal ───────────────────────────── */
    .ed-modal {
      max-width: 560px;
      width: 95%;
      padding: 0;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-top: 3.5px solid var(--accent2);
      box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6);
      background: #13131f;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .ed-close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      z-index: 20;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #fff;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }

    .ed-close-btn:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    .ed-content {
      position: relative;
      display: flex;
      flex-direction: column;
      max-height: 90vh;
    }

    .ed-banner {
      width: 100%;
      height: 200px;
      background-size: cover;
      background-position: center;
      position: relative;
      flex-shrink: 0;
    }

    .ed-banner-placeholder {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .ed-banner-emoji {
      font-size: 4.5rem;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
    }

    .ed-banner-fade {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 80px;
      background: linear-gradient(to bottom, transparent, #13131f);
    }

    .ed-body {
      padding: 20px 24px 24px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      overflow-y: auto;
    }

    .ed-header {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .ed-title-row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      flex-wrap: wrap;
    }

    .ed-title {
      margin: 0;
      font-size: 1.55rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.2;
      flex: 1;
      min-width: 0;
    }

    .ed-type-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: color-mix(in srgb, var(--tcolor) 18%, transparent);
      color: var(--tcolor);
      border: 1px solid color-mix(in srgb, var(--tcolor) 40%, transparent);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .ed-badges {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .ed-rejection {
      background: rgba(239, 68, 68, 0.09);
      border-left: 3px solid #ef4444;
      border-radius: 8px;
      padding: 12px 14px;
    }

    .ed-rej-label {
      display: block;
      font-size: 0.72rem;
      font-weight: 700;
      color: #ef4444;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 4px;
    }

    .ed-rejection p {
      margin: 0;
      color: #e2e8f0;
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .ed-section-label {
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: rgba(255, 255, 255, 0.35);
      margin-bottom: 6px;
    }

    .ed-description {
      margin: 0;
      color: rgba(255, 255, 255, 0.75);
      font-size: 0.95rem;
      line-height: 1.7;
    }

    .ed-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .ed-info-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 12px;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: background 0.2s;
    }

    .ed-info-card:hover {
      background: rgba(255, 255, 255, 0.07);
    }

    .ed-info-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: rgba(255, 255, 255, 0.05);
      color: rgba(255, 255, 255, 0.7);
      font-size: 1.1rem;
    }

    .ed-info-label {
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 2px;
    }

    .ed-info-value {
      font-weight: 600;
      font-size: 0.88rem;
      color: #fff;
    }

    .ed-info-accent .ed-info-label {
      color: var(--accent);
    }

    .ed-info-accent .ed-info-icon {
      background: rgba(110, 64, 242, 0.15);
      color: #a78bfa;
    }

    .ed-info-accent2 .ed-info-label {
      color: var(--accent2);
    }

    .ed-info-accent2 .ed-info-icon {
      background: rgba(34, 211, 238, 0.15);
      color: #22d3ee;
    }

    .ed-info-warning .ed-info-label {
      color: var(--warning);
    }

    .ed-info-warning .ed-info-icon {
      background: rgba(245, 158, 11, 0.15);
      color: #f59e0b;
    }

    .ed-info-danger .ed-info-label {
      color: #ef4444;
    }

    .ed-info-danger .ed-info-icon {
      background: rgba(239, 68, 68, 0.15);
      color: #ef4444;
    }

    .ed-footer {
      display: flex;
      align-items: center;
      gap: 8px;
      padding-top: 4px;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .ed-footer-label {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.35);
    }

    .ed-footer-name {
      font-size: 0.85rem;
      font-weight: 600;
      color: #fff;
    }
  </style>


  <!-- Published Schedule Modal -->
  <div class="modal-overlay" id="published-schedule-modal">
    <div class="modal" style="max-width:500px;">
      <div class="modal-header">
        <h3 class="modal-title" style="display:inline-flex; align-items:center; gap:8px;">
          <svg style="width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <span id="pub-modal-title">Publish Days</span>
        </h3>
        <button class="modal-close" onclick="closePublishedScheduleModal()">&times;</button>
      </div>
      <div class="modal-body">
        <p id="pub-modal-desc" style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;"></p>
        <div id="published-days-list" style="display:flex; flex-direction:column; gap:12px;"></div>
      </div>
      <div class="modal-footer"
        style="margin-top:24px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <button type="button" id="pub-unpublish-btn" class="btn btn-sm"
          style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.25);display:none;align-items:center;gap:6px;"
          onclick="unpublishEvent()">
          <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
          </svg>
          Unpublish
        </button>
        <div style="display:flex; gap:10px; margin-left:auto;">
          <button type="button" class="btn btn-ghost" onclick="closePublishedScheduleModal()">Cancel</button>
          <button type="button" class="btn btn-sm"
            style="background:rgba(139,92,246,0.15);color:#a78bfa;border:1px solid rgba(139,92,246,0.3);display:inline-flex;align-items:center;gap:6px;"
            onclick="savePublishedSchedule(false)">
            <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Draft
          </button>
          <button type="button" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;"
            onclick="savePublishedSchedule(true)">
            <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            Publish
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentPublishedScheduleEventId = null;

    function openPublishedScheduleModal(eventId) {
      const ev = allEvents.find(e => e.id === eventId);
      if (!ev) return;
      currentPublishedScheduleEventId = eventId;

      // Set translations
      document.getElementById('pub-modal-title').innerText = t('Publish Days');
      const statusIcon = ev.is_published
        ? `<svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2.5; display:inline-block; vertical-align:middle;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>`
        : `<svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; display:inline-block; vertical-align:middle;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`;
      document.getElementById('pub-modal-desc').innerHTML = t('published_schedule_desc') +
        `<div style="margin-top:10px;padding:8px 12px;border-radius:8px;font-size:0.8rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;${ev.is_published ? 'background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.2);' : 'background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);'}">${statusIcon} ${ev.is_published ? t('Published — Visible to public') : t('Draft — Not visible to public yet')}</div>`;

      // Show/hide unpublish button
      const unpubBtn = document.getElementById('pub-unpublish-btn');
      unpubBtn.style.display = ev.is_published ? 'inline-flex' : 'none';

      const schedule = Array.isArray(ev.external_schedule) ? ev.external_schedule :
        (Array.isArray(ev.internal_schedule) ? ev.internal_schedule : []);

      const publishedDays = Array.isArray(ev.published_schedule) ? ev.published_schedule : [];
      const container = document.getElementById('published-days-list');

      if (schedule.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding:30px; background:rgba(255,255,255,0.02); border-radius:12px; border:1px dashed rgba(255,255,255,0.1);">
                <div style="display:flex; justify-content:center; color:var(--text-muted); opacity:0.3; margin-bottom:10px;">
                  <svg style="width:36px; height:36px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <p style="color:var(--text-muted);">${t('No schedule found for this event.')}</p>
            </div>`;
      } else {
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        container.innerHTML = schedule.map((slot, index) => {
          const d = new Date(slot.date + 'T00:00:00');
          const isPublished = publishedDays.some(p => p.date === slot.date);
          const dayName = dayNames[d.getDay()] || 'Day';
          const monthName = monthNames[d.getMonth()] || '';
          const dayNum = d.getDate() || '';

          return `
                    <div class="publish-day-item" style="display:flex; align-items:center; gap:15px; padding:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:12px;">
                        <div style="min-width: 48px; text-align: center; background: rgba(139,92,246,0.1); border-radius: 8px; padding: 6px;">
                            <div style="font-size:0.6rem; font-weight:700; color:var(--accent2); text-transform:uppercase;">${dayName}</div>
                            <div style="font-size:1.1rem; font-weight:800; color:#fff; line-height:1;">${dayNum}</div>
                            <div style="font-size:0.55rem; color:var(--text-muted);">${monthName}</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600; color:#fff; font-size:0.95rem;">${slot.date}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:capitalize;">${slot.period ? t(slot.period.replace('_', ' ')) : (slot.start_time + ' - ' + slot.end_time)}</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" class="pub-toggle" data-date="${slot.date}" data-index="${index}" ${isPublished ? 'checked' : ''} onchange="this.parentElement.nextElementSibling.innerText = this.checked ? t('Public') : t('Setup'); this.parentElement.nextElementSibling.style.color = this.checked ? 'var(--accent2)' : 'var(--text-muted)';">
                            <span class="slider round"></span>
                        </label>
                        <span style="font-size:0.75rem; color:${isPublished ? 'var(--accent2)' : 'var(--text-muted)'}; font-weight:600; width:60px; text-align:right;">${isPublished ? t('Public') : t('Setup')}</span>
                    </div>
                `;
        }).join('');
      }

      document.getElementById('published-schedule-modal').classList.add('open');
    }

    function closePublishedScheduleModal() {
      document.getElementById('published-schedule-modal').classList.remove('open');
      currentPublishedScheduleEventId = null;
    }

    async function savePublishedSchedule(publish = false) {
      if (!currentPublishedScheduleEventId) return;

      const ev = allEvents.find(e => e.id === currentPublishedScheduleEventId);
      const schedule = ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule :
        (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : []);

      const toggles = document.querySelectorAll('.pub-toggle');
      const newPublishedSchedule = [];

      toggles.forEach(tog => {
        if (tog.checked) {
          const idx = parseInt(tog.dataset.index);
          newPublishedSchedule.push(schedule[idx]);
        }
      });

      if (publish && newPublishedSchedule.length === 0) {
        showToast(t('Please select at least one day to publish.'), 'error');
        return;
      }

      const res = await api.put(`/events/${currentPublishedScheduleEventId}/published-schedule`, {
        published_schedule: newPublishedSchedule,
        publish: publish
      });

      if (res.ok) {
        showToast(publish ? t('Event published successfully!') : t('published_schedule_success'), 'success');
        closePublishedScheduleModal();
        loadEvents();
      } else {
        showToast(res.data?.message || 'Error', 'error');
      }
    }

    function unpublishEvent() {
      if (!currentPublishedScheduleEventId) return;
      document.getElementById('unpublish-confirm-modal').classList.add('open');
    }

    function closeUnpublishConfirmModal() {
      document.getElementById('unpublish-confirm-modal').classList.remove('open');
    }

    document.getElementById('unpublish-confirm-btn').addEventListener('click', async () => {
      closeUnpublishConfirmModal();

      const ev = allEvents.find(e => e.id === currentPublishedScheduleEventId);
      const currentSchedule = ev.published_schedule || [];

      const res = await api.put(`/events/${currentPublishedScheduleEventId}/published-schedule`, {
        published_schedule: currentSchedule,
        publish: false
      });

      if (res.ok) {
        showToast(t('Event unpublished. It is no longer visible to the public.'), 'success');
        closePublishedScheduleModal();
        loadEvents();
      } else {
        showToast(res.data?.message || 'Error', 'error');
      }
    });
    function expandCapacity(eventId, currentCap, venueMax) {
      document.getElementById('expand-modal-current').textContent = currentCap;
      document.getElementById('expand-modal-max').textContent = venueMax;
      document.getElementById('expand-capacity-input').value = currentCap;
      document.getElementById('expand-capacity-event-id').value = eventId;
      document.getElementById('expand-capacity-venue-max').value = venueMax;
      document.getElementById('expand-capacity-modal').classList.add('open');
      setTimeout(() => document.getElementById('expand-capacity-input').focus(), 100);
    }

    function closeExpandCapacityModal() {
      document.getElementById('expand-capacity-modal').classList.remove('open');
    }

    async function submitExpandCapacity() {
      const eventId = document.getElementById('expand-capacity-event-id').value;
      const venueMax = parseInt(document.getElementById('expand-capacity-venue-max').value);
      const newCap = document.getElementById('expand-capacity-input').value;
      
      if (!newCap || newCap === "") return;
      
      const capInt = parseInt(newCap);
      if (isNaN(capInt) || capInt < 1) {
          showToast(t('Please enter a valid number.'), 'error');
          return;
      }

      if (capInt > venueMax) {
          showToast(t('Cannot exceed venue capacity') + ` (${venueMax}).`, 'error');
          return;
      }

      try {
          const res = await api.patch(`/events/${eventId}/capacity`, { capacity: capInt });
          if (res.ok) {
              closeExpandCapacityModal();
              showToast(t('Capacity expanded successfully!'), 'success');
              // Update local data
              const ev = allEvents.find(e => e.id == eventId);
              if (ev) ev.capacity = capInt;
              
              // Update UI in modal if open
              const capValEl = document.getElementById(`det-capacity-${eventId}`);
              if (capValEl) capValEl.textContent = capInt;
              
              // Refresh list to update capacity column
              applyFilter();
          } else {
              showToast(res.data?.message || 'Failed to update capacity.', 'error');
          }
      } catch (err) {
        console.error(err);
        showToast('An error occurred.', 'error');
      }
    }

    function navigateToProfile(id) { showProfileModal(id); }

    async function showProfileModal(userId) {
      const modal = document.getElementById('profile-details-modal');
      const content = document.getElementById('profile-details-content');
      modal.classList.add('open');
      content.innerHTML = '<div class="spinner" style="margin:40px auto"></div>';

      try {
        const res = await api.get('/profile/' + userId);
        if (!res.ok) {
          content.innerHTML = `<div style="text-align:center;color:var(--danger);padding:20px;">${t('Failed to load profile')}</div>`;
          return;
        }
        const u = res.data.user;
        const p = u.profile || {};

        let avatar = '/images/default-avatar.png';
        if (u.image && u.image.trim() !== '') {
          avatar = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
        } else if (u.avatar && u.avatar.trim() !== '') {
          avatar = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
        } else if (p.logo && p.logo.trim() !== '') {
          avatar = (p.logo.startsWith('http') || p.logo.startsWith('/')) ? p.logo : '/' + p.logo;
        }

        const roleStyles = {
          'Admin': 'background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3)',
          'Event Manager': 'background:rgba(110,64,242,.15);color:#a78bfa;border:1px solid rgba(110,64,242,.3)',
          'Sponsor': 'background:rgba(234,179,8,.15);color:#eab308;border:1px solid rgba(234,179,8,.3)',
          'Company': 'background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)',
          'Attendee': 'background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3)',
          'Assistant': 'background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3)',
        };

        const roleStyle = roleStyles[u.role] || 'background:rgba(255,255,255,.1);color:#fff';

        // Build contacts HTML
        let contactsHtml = '';
        if (u.contact_email) {
          contactsHtml += `
            <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.02);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);">
              <span style="display:inline-flex;color:var(--text-muted);">${getContactIcon('email')}</span>
              <div>
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;">${t('Contact Email')}</div>
                <div style="font-weight:500;font-size:0.85rem;">${u.contact_email}</div>
              </div>
            </div>
          `;
        }
        if (u.phone) {
          contactsHtml += `
            <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.02);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);">
              <span style="display:inline-flex;color:var(--text-muted);">${getContactIcon('phone')}</span>
              <div>
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;">${t('Phone')}</div>
                <div style="font-weight:500;font-size:0.85rem;"><a href="tel:${u.phone}" style="color:inherit;text-decoration:none;">${u.phone}</a></div>
              </div>
            </div>
          `;
        }

        // Build social links HTML
        let socialHtml = '';
        if (u.social_links && Object.keys(u.social_links).length > 0) {
          let linksHtml = '';
          for (let [pKey, link] of Object.entries(u.social_links)) {
            if (link) {
              const platform = pKey.split('_')[0];
              const icon = getContactIcon(platform);
              linksHtml += `
                <a href="${link.startsWith('http') ? link : 'https://' + link}" target="_blank" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);display:inline-flex;align-items:center;justify-content:center;color:var(--text-muted);text-decoration:none;font-size:1.1rem;transition:all 0.2s;" onmouseover="this.style.background='rgba(110,64,242,0.1)';this.style.borderColor='var(--accent)';" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.08)';">
                  ${icon}
                </a>
              `;
            }
          }
          if (linksHtml) {
            socialHtml = `
              <div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">${t('Social Profiles & Links')}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">${linksHtml}</div>
              </div>
            `;
          }
        }

        const bioText = (u.role === 'Sponsor' || u.role === 'Company' ? p.company_description : p.bio) || u.bio || t('No bio provided yet.');

        content.innerHTML = `
          <!-- Header -->
          <div style="display:flex;align-items:center;gap:14px;">
            <img src="${avatar}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" />
            <div>
              <h4 style="margin:0;font-size:1.25rem;font-weight:800;color:#fff;" class="i18n-skip">${u.name}</h4>
              <div style="display:flex;gap:6px;margin-top:6px;align-items:center;flex-wrap:wrap;">
                <span style="${roleStyle};padding:2px 8px;border-radius:12px;font-size:0.65rem;font-weight:700;text-transform:uppercase;">${t(u.role)}</span>
                ${p.company_type ? `<span style="background:rgba(110,64,242,0.1);color:var(--accent);border:1px solid rgba(110,64,242,0.2);padding:2px 8px;border-radius:12px;font-size:0.65rem;font-weight:700;">${p.company_type}</span>` : ''}
              </div>
            </div>
          </div>

          <!-- About -->
          <div style="margin-top:4px;">
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;">${t('About')}</div>
            <p style="margin:0;color:rgba(255,255,255,0.75);font-size:0.88rem;line-height:1.6;" class="i18n-skip">${bioText}</p>
          </div>

          <!-- Contact Info -->
          ${contactsHtml ? `
            <div>
              <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">${t('Contact Information')}</div>
              <div style="display:flex;flex-direction:column;gap:8px;">${contactsHtml}</div>
            </div>
          ` : ''}

          <!-- Socials -->
          ${socialHtml}
        `;
      } catch (err) {
        console.error(err);
        content.innerHTML = `<div style="text-align:center;color:var(--danger);padding:20px;">${t('Failed to load profile')}</div>`;
      }
    }

    function closeProfileModal() {
      document.getElementById('profile-details-modal').classList.remove('open');
      document.getElementById('profile-details-content').innerHTML = '';
    }
  </script>


</body>

</html>