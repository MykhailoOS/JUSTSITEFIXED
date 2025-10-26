# Testing & Deployment Checklist - Three-Panel Editor

Use this checklist to verify the three-panel editor is working correctly before deploying to production.

## 🔍 Pre-Deployment Testing

### A. File Verification

- [x] `buildr.php` exists (67KB, 1261 lines)
- [x] `assets/js/editor-canvas.js` exists (22KB, 532 lines)
- [x] `assets/js/editor-library.js` exists (1.2KB, 44 lines)
- [x] `assets/js/editor-inspector.js` exists (3.7KB, 134 lines)
- [x] `.htaccess` updated with CSP headers
- [x] Documentation files in `/docs/` folder
- [ ] All files have correct permissions (644 for files, 755 for directories)

### B. PHP Syntax Check

```bash
php -l buildr.php
```

Expected output: `No syntax errors detected`

- [ ] PHP syntax validated
- [ ] No parse errors
- [ ] All includes/requires exist

### C. Browser Compatibility Testing

Test in each browser:

#### Chrome (Recommended)
- [ ] Open `buildr.php`
- [ ] All panels visible
- [ ] Drag-and-drop works
- [ ] Inspector updates work
- [ ] Console has no errors
- [ ] CSP headers present (check Network tab)

#### Firefox
- [ ] Open `buildr.php`
- [ ] All panels visible
- [ ] Drag-and-drop works
- [ ] Inspector updates work
- [ ] Console has no errors

#### Safari
- [ ] Open `buildr.php`
- [ ] All panels visible
- [ ] Backdrop-filter blur works
- [ ] Drag-and-drop works
- [ ] Inspector updates work

#### Edge
- [ ] Open `buildr.php`
- [ ] All panels visible
- [ ] Drag-and-drop works
- [ ] Inspector updates work

### D. Responsive Testing

#### Desktop (1920x1080)
- [ ] Three panels visible side-by-side
- [ ] Library panel 320px wide (left)
- [ ] Inspector panel 320px wide (right)
- [ ] Canvas flexible width (center)
- [ ] All controls accessible

#### Laptop (1366x768)
- [ ] Three panels visible
- [ ] Panels proportional
- [ ] No horizontal scrolling
- [ ] All features work

#### Tablet (768x1024)
- [ ] Canvas full width
- [ ] Library slides from left
- [ ] Inspector slides from right
- [ ] Floating toggle buttons visible
- [ ] Touch targets at least 44px

#### Mobile (375x667)
- [ ] Canvas full width
- [ ] Library full-screen slide-over
- [ ] Inspector full-screen slide-over
- [ ] Touch-optimized controls
- [ ] No text overflow

### E. Functionality Testing

#### Library Panel
- [ ] Search box works
- [ ] Categories expand/collapse
- [ ] All 5 categories present:
  - [ ] Layout (Section, Container, Row, Column)
  - [ ] Content (Heading, Paragraph, Button, Link, List, Divider)
  - [ ] Media (Image, Video, Icon)
  - [ ] Forms (Input, Textarea, Select, Checkbox, Radio)
  - [ ] Components (Card, Navbar, Tabs, Accordion, Modal)
- [ ] Element cards clickable
- [ ] Element cards draggable
- [ ] Icons render correctly

#### Canvas Panel
- [ ] iframe loads correctly
- [ ] Default placeholder visible
- [ ] Elements can be added (drag)
- [ ] Elements can be added (click)
- [ ] Elements selectable
- [ ] Selected element shows blue outline
- [ ] Click canvas background deselects
- [ ] Viewport switcher works:
  - [ ] Desktop view (1280px)
  - [ ] Tablet view (768px)
  - [ ] Mobile view (375px)
- [ ] Zoom controls work:
  - [ ] Zoom in (+)
  - [ ] Zoom out (-)
  - [ ] Display shows percentage
- [ ] Grid toggle works
- [ ] Snapping toggle works

#### Inspector Panel
- [ ] Shows when element selected
- [ ] Hides when no selection
- [ ] All 5 tabs present:
  - [ ] Content tab
  - [ ] Layout tab
  - [ ] Typography tab
  - [ ] Accessibility tab
  - [ ] Advanced tab
- [ ] Tab switching works

#### Inspector - Content Tab
- [ ] Text content textarea works
- [ ] Updates reflect in canvas
- [ ] Tag selector works
- [ ] Tag change updates canvas

#### Inspector - Layout Tab
- [ ] Visual box model displays
- [ ] Margin inputs work (4 sides)
- [ ] Padding inputs work (4 sides)
- [ ] Lock button works
- [ ] When locked, all sides sync
- [ ] Unit selector works (px/rem/%)
- [ ] Presets work (0/8/16/24/32)
- [ ] Width input works
- [ ] Height input works
- [ ] Changes apply to canvas

#### Inspector - Typography Tab
- [ ] Font size input works
- [ ] Font weight select works
- [ ] Text color picker works
- [ ] Text align buttons work:
  - [ ] Left align
  - [ ] Center align
  - [ ] Right align
- [ ] Changes apply to canvas

#### Inspector - Accessibility Tab
- [ ] ARIA label input works
- [ ] Role selector works
- [ ] Focusable checkbox works
- [ ] Attributes apply to element

#### Inspector - Advanced Tab
- [ ] Element ID input works
- [ ] CSS classes input works
- [ ] Custom CSS textarea works
- [ ] Delete button works
- [ ] Confirmation dialog appears
- [ ] Element removes from canvas

#### Top Bar
- [ ] Logo clickable (goes to profile)
- [ ] Project name displays
- [ ] Undo button present
- [ ] Redo button present
- [ ] Viewport buttons work
- [ ] Zoom controls work
- [ ] Save button works
- [ ] Preview button works
- [ ] Deploy button works
- [ ] User dropdown works:
  - [ ] Profile link
  - [ ] Dashboard link
  - [ ] Logout link

#### Toast Notifications
- [ ] Success toast shows (green)
- [ ] Error toast shows (red)
- [ ] Info toast shows (blue)
- [ ] Auto-dismiss after 5s
- [ ] Close button works
- [ ] Multiple toasts stack

### F. Security Testing

#### CSP Headers
```bash
curl -I https://yourdomain.com/buildr.php | grep -i "content-security-policy"
```

- [ ] CSP header present
- [ ] `frame-ancestors 'self'` included
- [ ] Allowed sources listed correctly

#### X-Frame-Options
```bash
curl -I https://yourdomain.com/buildr.php | grep -i "x-frame-options"
```

- [ ] Header present
- [ ] Value is `SAMEORIGIN`

#### iframe Sandbox
Open browser console and check:
```javascript
document.getElementById('canvasFrame').sandbox
```

- [ ] Sandbox attribute present
- [ ] Contains `allow-scripts`
- [ ] Contains `allow-same-origin`
- [ ] Does NOT contain `allow-forms`
- [ ] Does NOT contain `allow-top-navigation`

#### postMessage Security
- [ ] No direct DOM access from parent to iframe
- [ ] All communication via postMessage
- [ ] Messages validated in receiver
- [ ] No sensitive data in messages

### G. Performance Testing

#### Load Time
Use browser DevTools > Network tab:

- [ ] First Contentful Paint < 2s
- [ ] Time to Interactive < 3s
- [ ] Full page load < 5s
- [ ] Alpine.js loads successfully
- [ ] Tailwind CSS loads successfully

#### Memory Usage
Use browser DevTools > Performance Monitor:

- [ ] Initial memory < 100MB
- [ ] Stable after interactions
- [ ] No memory leaks after 5 minutes

#### Interaction Performance
- [ ] Element addition < 100ms
- [ ] Inspector updates < 200ms (debounced)
- [ ] Viewport switch < 100ms
- [ ] Zoom change < 100ms
- [ ] No UI freezing

### H. Data Persistence Testing

#### Save Project
- [ ] Click Save button
- [ ] Modal/prompt appears (or toast)
- [ ] Enter project name
- [ ] Project saves successfully
- [ ] Success toast appears
- [ ] Project ID assigned

#### Load Project
- [ ] Navigate to `buildr.php?load=PROJECT_ID`
- [ ] Project loads
- [ ] All elements present
- [ ] Canvas HTML restored
- [ ] Project name displays

#### Edit and Re-save
- [ ] Load existing project
- [ ] Add new element
- [ ] Save project
- [ ] Refresh page
- [ ] New element persists

### I. Keyboard Shortcuts

- [ ] `Ctrl/Cmd + Z` triggers undo
- [ ] `Ctrl/Cmd + Shift + Z` triggers redo
- [ ] `Ctrl/Cmd + S` triggers save
- [ ] `Escape` closes dropdowns
- [ ] `Tab` navigates through controls
- [ ] `Enter` activates buttons

### J. Accessibility Testing

#### Keyboard Navigation
- [ ] All interactive elements focusable
- [ ] Focus visible (blue ring)
- [ ] Logical tab order
- [ ] No keyboard traps
- [ ] Skip links present (optional)

#### Screen Reader
Test with NVDA/JAWS/VoiceOver:

- [ ] All buttons announced
- [ ] Form labels read correctly
- [ ] ARIA labels work
- [ ] Role attributes work
- [ ] State changes announced

#### Color Contrast
- [ ] Text meets WCAG AA (4.5:1)
- [ ] UI elements visible
- [ ] Focus indicators high contrast

### K. Error Handling

#### Network Errors
- [ ] API failure shows error toast
- [ ] Retry option available
- [ ] No data loss

#### Invalid Input
- [ ] Invalid values rejected
- [ ] Error messages clear
- [ ] Input validated

#### Browser Console
- [ ] No JavaScript errors
- [ ] No 404 errors
- [ ] No CORS errors
- [ ] No CSP violations (except expected)

## 🚀 Deployment Checklist

### Pre-Production

- [ ] All tests passed
- [ ] Documentation reviewed
- [ ] Code reviewed
- [ ] Security audit complete
- [ ] Performance benchmarks met

### Staging Deployment

- [ ] Deploy to staging server
- [ ] Verify all files uploaded
- [ ] Check file permissions
- [ ] Test CSP headers
- [ ] Full regression test
- [ ] User acceptance testing (UAT)

### Production Deployment

#### Backup
- [ ] Backup database
- [ ] Backup existing files
- [ ] Document rollback procedure

#### Upload
- [ ] Upload `buildr.php`
- [ ] Upload `assets/js/editor-*.js`
- [ ] Upload documentation (optional)
- [ ] Update `.htaccess`
- [ ] Verify file permissions

#### Configuration
- [ ] Set correct base path
- [ ] Configure database connection
- [ ] Enable CSP headers
- [ ] Test CSP doesn't break functionality

#### Verification
- [ ] Access `buildr.php` directly
- [ ] Test from dashboard link
- [ ] Test project load
- [ ] Test project save
- [ ] Monitor error logs

#### Monitoring
- [ ] Setup error tracking
- [ ] Monitor server logs
- [ ] Track user adoption
- [ ] Collect feedback

### Post-Production

- [ ] Announce to users
- [ ] Update documentation links
- [ ] Monitor for issues
- [ ] Collect metrics
- [ ] Plan next iteration

## 🐛 Known Issues & Workarounds

### Issue Template

**Issue:** Description  
**Severity:** Critical / High / Medium / Low  
**Workaround:** Temporary solution  
**Fix:** Permanent solution (if known)  
**Status:** Open / In Progress / Resolved

---

(Add discovered issues here during testing)

## 📊 Test Results Summary

### Testing Date: _______________
### Tested By: _______________
### Environment: Development / Staging / Production

| Category | Total Tests | Passed | Failed | Skipped |
|----------|-------------|--------|--------|---------|
| Files | 6 | 0 | 0 | 0 |
| PHP Syntax | 1 | 0 | 0 | 0 |
| Browsers | 16 | 0 | 0 | 0 |
| Responsive | 16 | 0 | 0 | 0 |
| Functionality | 75+ | 0 | 0 | 0 |
| Security | 12 | 0 | 0 | 0 |
| Performance | 10 | 0 | 0 | 0 |
| Data | 6 | 0 | 0 | 0 |
| Keyboard | 6 | 0 | 0 | 0 |
| Accessibility | 15 | 0 | 0 | 0 |
| Errors | 6 | 0 | 0 | 0 |

**Overall Pass Rate:** ____%

### Critical Issues Found: _____
### Blockers: _____
### Ready for Deployment: Yes / No

## 📝 Notes

(Add any additional observations, recommendations, or concerns here)

---

## ✅ Final Sign-Off

- [ ] All critical tests passed
- [ ] All blockers resolved
- [ ] Security verified
- [ ] Performance acceptable
- [ ] Documentation complete
- [ ] Team approval obtained

**Approved By:** _______________  
**Date:** _______________  
**Signature:** _______________

---

**Next Steps:**
1. Complete all checklist items
2. Document any issues found
3. Fix critical issues
4. Deploy to staging
5. Conduct UAT
6. Deploy to production
7. Monitor and iterate

**Good luck! 🚀**
