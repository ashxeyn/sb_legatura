/**
 * Milestone Progress Report — Interactive handler.
 * Handles Full Details Modal, tab switching, dropdown, accordion,
 * milestone navigation, and progress report modal triggers.
 */
document.addEventListener('DOMContentLoaded', () => {
    const config = window.__milestoneProgressConfig || {};

    // ══════════════════════════════════════════════════════════════════
    // FULL DETAILS MODAL
    // ══════════════════════════════════════════════════════════════════
    const fullDetailsModal = document.getElementById('fullDetailsModal');
    const viewFullBtn = document.getElementById('viewFullDetailsBtn');
    const closeFullDetailsBtn = document.getElementById('closeFullDetailsModal');
    const cancelFullDetailsBtn = document.getElementById('cancelFullDetailsModal');

    // ── FDM Tab Switching ─────────────────────────────────────────────
    const fdmTabs = document.querySelectorAll('.fdm-tab');
    const fdmPanels = document.querySelectorAll('.fdm-tab-content');

    fdmTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.fdmTab;
            fdmTabs.forEach(t => t.classList.remove('active'));
            fdmPanels.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            const panel = document.getElementById('fdm-tab-' + target);
            if (panel) {
                panel.classList.add('active');
                panel.style.animation = 'none';
                panel.offsetHeight;
                panel.style.animation = '';
            }
        });
    });

    function openFullDetails(tabName) {
        if (!fullDetailsModal) return;
        fullDetailsModal.style.display = 'flex';
        fullDetailsModal.style.animation = 'none';
        fullDetailsModal.offsetHeight;
        fullDetailsModal.style.animation = '';
        document.body.style.overflow = 'hidden';

        // Scroll body to top
        const body = fullDetailsModal.querySelector('.mdp-modal-body');
        if (body) body.scrollTop = 0;

        // Switch to requested tab
        if (tabName) {
            const targetTab = document.querySelector(`.fdm-tab[data-fdm-tab="${tabName}"]`);
            if (targetTab) targetTab.click();
        }
    }

    function closeFullDetails() {
        if (!fullDetailsModal) return;
        const inner = fullDetailsModal.querySelector('.mdp-modal');
        if (inner) {
            inner.style.animation = 'none';
            inner.offsetHeight;
            inner.style.transition = 'opacity 0.2s, transform 0.2s';
            inner.style.opacity = '0';
            inner.style.transform = 'translateY(20px) scale(0.97)';
        }
        fullDetailsModal.style.transition = 'opacity 0.2s';
        fullDetailsModal.style.opacity = '0';
        setTimeout(() => {
            fullDetailsModal.style.display = 'none';
            fullDetailsModal.style.opacity = '';
            fullDetailsModal.style.transition = '';
            if (inner) {
                inner.style.opacity = '';
                inner.style.transform = '';
                inner.style.transition = '';
                inner.style.animation = '';
            }
            document.body.style.overflow = '';
        }, 220);
    }

    if (viewFullBtn) viewFullBtn.addEventListener('click', () => openFullDetails('info'));
    if (closeFullDetailsBtn) closeFullDetailsBtn.addEventListener('click', closeFullDetails);
    if (cancelFullDetailsBtn) cancelFullDetailsBtn.addEventListener('click', closeFullDetails);

    // Quick financial card → switch to payments tab in FDM
    const fdmGoPayBtn = document.getElementById('fdmGoToPaymentsTab');
    if (fdmGoPayBtn) {
        fdmGoPayBtn.addEventListener('click', () => {
            const payTab = document.querySelector('.fdm-tab[data-fdm-tab="payments"]');
            if (payTab) payTab.click();
            // Scroll body to top
            const body = fullDetailsModal?.querySelector('.mdp-modal-body');
            if (body) body.scrollTop = 0;
        });
    }

    // "Go to payments tab" link from main view
    const goToPaymentsLink = document.getElementById('goToPaymentsTab');
    if (goToPaymentsLink) {
        goToPaymentsLink.addEventListener('click', () => openFullDetails('payments'));
    }

    // ── FDM Financial Accordion (smooth) ──────────────────────────────
    const fdmAccToggle = document.getElementById('fdmFinAccordionToggle');
    if (fdmAccToggle) {
        const accordion = fdmAccToggle.closest('.fdm-accordion');
        const accBody = document.getElementById('fdmFinAccordionBody');
        if (accBody) {
            accBody.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            fdmAccToggle.addEventListener('click', () => {
                const isOpen = accBody.style.display !== 'none';
                if (isOpen) {
                    accBody.style.opacity = '0';
                    accBody.style.transform = 'translateY(-4px)';
                    setTimeout(() => {
                        accBody.style.display = 'none';
                        accordion.classList.remove('open');
                    }, 200);
                } else {
                    accBody.style.display = 'block';
                    accBody.style.opacity = '0';
                    accBody.style.transform = 'translateY(-4px)';
                    accordion.classList.add('open');
                    requestAnimationFrame(() => {
                        accBody.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        accBody.style.opacity = '1';
                        accBody.style.transform = 'translateY(0)';
                    });
                }
            });
        }
    }

    // ── FDM Date History Toggle (smooth) ──────────────────────────────
    const fdmDhToggle = document.getElementById('fdmDateHistoryToggle');
    const fdmDhBody = document.getElementById('fdmDateHistoryBody');
    if (fdmDhToggle && fdmDhBody) {
        fdmDhBody.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        fdmDhToggle.addEventListener('click', () => {
            const isOpen = fdmDhBody.style.display !== 'none';
            if (isOpen) {
                fdmDhBody.style.opacity = '0';
                fdmDhBody.style.transform = 'translateY(-4px)';
                setTimeout(() => {
                    fdmDhBody.style.display = 'none';
                    fdmDhToggle.classList.remove('open');
                }, 200);
            } else {
                fdmDhBody.style.display = 'block';
                fdmDhBody.style.opacity = '0';
                fdmDhBody.style.transform = 'translateY(-4px)';
                fdmDhToggle.classList.add('open');
                requestAnimationFrame(() => {
                    fdmDhBody.style.opacity = '1';
                    fdmDhBody.style.transform = 'translateY(0)';
                });
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // EDIT PAYMENT DUE DATE (inline form)
    // ══════════════════════════════════════════════════════════════════
    const editDueDateBtn = document.getElementById('fdmEditDueDateBtn');
    const dueDateDisplay = document.getElementById('fdmDueDateDisplay');
    const dueDateEdit = document.getElementById('fdmDueDateEdit');
    const dueDateInput = document.getElementById('fdmDueDateInput');
    const extDateInput = document.getElementById('fdmExtDateInput');
    const saveDueDateBtn = document.getElementById('fdmSaveDueDateBtn');
    const cancelDueDateBtn = document.getElementById('fdmCancelDueDateBtn');
    const dueDateError = document.getElementById('fdmDueDateError');
    const dueDateSuccess = document.getElementById('fdmDueDateSuccess');

    function showDueDateEditMode() {
        if (!dueDateDisplay || !dueDateEdit) return;
        dueDateDisplay.style.opacity = '0';
        dueDateDisplay.style.transform = 'translateY(-4px)';
        setTimeout(() => {
            dueDateDisplay.style.display = 'none';
            dueDateEdit.style.display = 'block';
            dueDateEdit.style.opacity = '0';
            dueDateEdit.style.transform = 'translateY(6px)';
            requestAnimationFrame(() => {
                dueDateEdit.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                dueDateEdit.style.opacity = '1';
                dueDateEdit.style.transform = 'translateY(0)';
            });
            // Focus the date input after transition
            setTimeout(() => { if (dueDateInput) dueDateInput.focus(); }, 280);
        }, 200);
        // Clear messages
        if (dueDateError) { dueDateError.style.display = 'none'; dueDateError.textContent = ''; }
        if (dueDateSuccess) { dueDateSuccess.style.display = 'none'; dueDateSuccess.textContent = ''; }
    }

    function showDueDateDisplayMode() {
        if (!dueDateDisplay || !dueDateEdit) return;
        dueDateEdit.style.opacity = '0';
        dueDateEdit.style.transform = 'translateY(-4px)';
        setTimeout(() => {
            dueDateEdit.style.display = 'none';
            dueDateDisplay.style.display = 'block';
            dueDateDisplay.style.opacity = '0';
            dueDateDisplay.style.transform = 'translateY(6px)';
            requestAnimationFrame(() => {
                dueDateDisplay.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                dueDateDisplay.style.opacity = '1';
                dueDateDisplay.style.transform = 'translateY(0)';
            });
        }, 200);
    }

    if (editDueDateBtn) {
        editDueDateBtn.addEventListener('click', showDueDateEditMode);
        // Init transitions
        [dueDateDisplay, dueDateEdit].forEach(el => {
            if (el) el.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        });
    }
    if (cancelDueDateBtn) cancelDueDateBtn.addEventListener('click', showDueDateDisplayMode);

    // Update extension date min when due date changes
    if (dueDateInput && extDateInput) {
        dueDateInput.addEventListener('change', () => {
            if (dueDateInput.value) {
                extDateInput.min = dueDateInput.value;
                // Clear extension if it's now before the due date
                if (extDateInput.value && extDateInput.value <= dueDateInput.value) {
                    extDateInput.value = '';
                }
            }
        });
    }

    // Save due date via AJAX
    if (saveDueDateBtn) {
        saveDueDateBtn.addEventListener('click', async () => {
            if (!dueDateInput || !dueDateInput.value) {
                if (dueDateError) {
                    dueDateError.textContent = 'Please select a due date.';
                    dueDateError.style.display = 'flex';
                }
                shakeElement(dueDateInput);
                return;
            }

            // Validate extension date
            if (extDateInput && extDateInput.value && extDateInput.value <= dueDateInput.value) {
                if (dueDateError) {
                    dueDateError.textContent = 'Extension date must be after the due date.';
                    dueDateError.style.display = 'flex';
                }
                shakeElement(extDateInput);
                return;
            }

            // Show loading
            const origHTML = saveDueDateBtn.innerHTML;
            saveDueDateBtn.innerHTML = '<span class="mdp-spinner"></span>';
            saveDueDateBtn.disabled = true;
            if (dueDateError) dueDateError.style.display = 'none';
            if (dueDateSuccess) dueDateSuccess.style.display = 'none';

            try {
                const body = {
                    user_id: config.userId,
                    settlement_due_date: dueDateInput.value,
                };
                if (extDateInput && extDateInput.value) {
                    body.extension_date = extDateInput.value;
                }

                const res = await fetch(config.routes.settlementDueDate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    // Update the display with new date
                    const newDate = new Date(dueDateInput.value + 'T00:00:00');
                    const formatted = newDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

                    const dateText = document.getElementById('fdmDueDateText');
                    const notSet = document.getElementById('fdmDueDateNotSet');
                    const dateValue = document.getElementById('fdmDueDateValue');

                    if (dateText) {
                        dateText.textContent = formatted;
                    } else if (notSet) {
                        // Replace "Not set yet" with the date display
                        notSet.remove();
                        const parent = dueDateDisplay.querySelector('.flex-1');
                        if (parent) {
                            const newDiv = document.createElement('div');
                            newDiv.className = 'flex items-center gap-2 mt-1';
                            newDiv.id = 'fdmDueDateValue';
                            newDiv.innerHTML = `<span class="text-sm text-gray-700 font-medium" id="fdmDueDateText">${formatted}</span>`;
                            parent.appendChild(newDiv);
                        }
                    }

                    // Update extension display
                    const extInfo = document.getElementById('fdmExtensionInfo');
                    if (extDateInput && extDateInput.value) {
                        const extDate = new Date(extDateInput.value + 'T00:00:00');
                        const extFormatted = extDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        if (extInfo) {
                            extInfo.querySelector('span').textContent = `Extended to ${extFormatted}`;
                        } else {
                            const parent = dueDateDisplay.querySelector('.flex-1');
                            if (parent) {
                                const newExt = document.createElement('div');
                                newExt.className = 'flex items-center gap-1 mt-1';
                                newExt.id = 'fdmExtensionInfo';
                                newExt.innerHTML = `<i class="fi fi-rr-arrow-right text-xs text-amber-500"></i><span class="text-xs text-amber-500 font-medium">Extended to ${extFormatted}</span>`;
                                parent.appendChild(newExt);
                            }
                        }
                    } else if (extInfo) {
                        extInfo.remove();
                    }

                    if (dueDateSuccess) {
                        dueDateSuccess.innerHTML = '<i class="fi fi-rr-check-circle"></i> Due date updated successfully';
                        dueDateSuccess.style.display = 'flex';
                    }

                    // Switch back to display after a brief delay
                    setTimeout(() => showDueDateDisplayMode(), 1200);
                } else {
                    const msg = data.message || 'Failed to update due date.';
                    if (dueDateError) {
                        dueDateError.textContent = msg;
                        dueDateError.style.display = 'flex';
                    }
                }
            } catch (err) {
                console.error('Settlement due date error:', err);
                if (dueDateError) {
                    dueDateError.textContent = 'Network error. Please try again.';
                    dueDateError.style.display = 'flex';
                }
            } finally {
                saveDueDateBtn.innerHTML = origHTML;
                saveDueDateBtn.disabled = false;
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // SEND PAYMENT FROM FULL DETAILS MODAL
    // ══════════════════════════════════════════════════════════════════
    const fdmSendPaymentBtn = document.getElementById('fdmSendPaymentBtn');
    if (fdmSendPaymentBtn) {
        fdmSendPaymentBtn.addEventListener('click', () => {
            // Close Full Details Modal first, then open Payment Receipt Modal
            closeFullDetails();
            setTimeout(() => {
                const paymentModal = document.getElementById('paymentReceiptModal');
                if (paymentModal) {
                    paymentModal.style.display = 'flex';
                    paymentModal.style.animation = 'none';
                    paymentModal.offsetHeight;
                    paymentModal.style.animation = '';
                    document.body.style.overflow = 'hidden';
                }
            }, 280);
        });
    }

    // ── Dropdown Menu ─────────────────────────────────────────────────
    const menuBtn = document.getElementById('reportMenuBtn');
    const dropdown = document.getElementById('reportDropdown');
    if (menuBtn && dropdown) {
        menuBtn.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = dropdown.style.display !== 'none';
            dropdown.style.display = isOpen ? 'none' : '';
        });
        document.addEventListener('click', e => {
            if (!menuBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        dropdown.querySelectorAll('.milestone-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const action = item.dataset.action;
                dropdown.style.display = 'none';
                if (action === 'send-report') {
                    if (window.openSendReportModal) {
                        // Find current milestone item title from allItems
                        const currentItem = (config.allItems || []).find(
                            i => String(i.item_id) === String(config.itemId)
                        );
                        const projectData = {
                            projectId: config.projectId,
                            projectTitle: config.projectTitle || config.milestoneTitle || '',
                            currentItemId: config.itemId || null,
                            currentItemTitle: currentItem
                                ? (currentItem.milestone_item_title || '')
                                : (config.milestoneTitle || ''),
                        };
                        window.openSendReportModal(projectData, config.allItems || []);
                    }
                } else if (action === 'report-history') {
                    if (window.openReportHistoryModal) {
                        window.openReportHistoryModal(config.disputes || []);
                    }
                } else if (action === 'summary') {
                    const psmModal = document.getElementById('projectSummaryModal');
                    if (psmModal) openModal(psmModal);
                }
            });
        });
    }

    // ── Progress Report View Links ────────────────────────────────────
    document.querySelectorAll('.mdp-timeline-btn, .mdp-timeline-item').forEach(el => {
        el.addEventListener('click', e => {
            if (el.classList.contains('mdp-timeline-item') && e.target.closest('.mdp-timeline-btn')) return;
            const idx = parseInt(el.dataset.reportIndex);
            const reports = config.progressReports || [];
            const report = reports[idx];
            if (!report) return;
            if (window.openOwnerProgressReportModal) {
                window.openOwnerProgressReportModal(report, {
                    milestoneTitle: `Milestone ${config.seqNum || 1}: ${config.milestoneTitle || ''}`,
                    sequenceNumber: config.seqNum || 1,
                });
            }
        });
    });

    // ── Staggered card entrance animation ─────────────────────────────
    document.querySelectorAll('.mdp-card, .mdp-accordion, .mdp-payment-card, .mdp-timeline-item, .fdm-card, .fdm-accordion, .fdm-payment-card').forEach((card, i) => {
        card.style.animationDelay = `${i * 0.06}s`;
    });

    // ══════════════════════════════════════════════════════════════════
    // PAYMENT RECEIPT MODAL
    // ══════════════════════════════════════════════════════════════════
    const paymentModal = document.getElementById('paymentReceiptModal');
    const openPaymentBtn = document.getElementById('openPaymentModalBtn');
    const closePaymentBtn = document.getElementById('closePaymentModal');
    const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
    const paymentForm = document.getElementById('paymentReceiptForm');
    const amountInput = document.getElementById('paymentAmount');
    const overWarning = document.getElementById('overAmountWarning');

    function openModal(modal) {
        if (!modal) return;
        modal.style.display = 'flex';
        modal.style.animation = 'none';
        modal.offsetHeight;
        modal.style.animation = '';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        const inner = modal.querySelector('.mdp-modal');
        if (inner) {
            inner.style.animation = 'none';
            inner.offsetHeight;
            inner.style.transition = 'opacity 0.2s, transform 0.2s';
            inner.style.opacity = '0';
            inner.style.transform = 'translateY(20px) scale(0.97)';
        }
        modal.style.transition = 'opacity 0.2s';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.opacity = '';
            modal.style.transition = '';
            if (inner) {
                inner.style.opacity = '';
                inner.style.transform = '';
                inner.style.transition = '';
                inner.style.animation = '';
            }
            document.body.style.overflow = '';
        }, 220);
    }

    // Open / close payment modal
    if (openPaymentBtn) openPaymentBtn.addEventListener('click', () => openModal(paymentModal));
    if (closePaymentBtn) closePaymentBtn.addEventListener('click', () => closeModal(paymentModal));
    if (cancelPaymentBtn) cancelPaymentBtn.addEventListener('click', () => closeModal(paymentModal));

    // Close modals on overlay click
    document.querySelectorAll('.mdp-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) closeModal(overlay);
        });
    });

    // Close modals on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.mdp-modal-overlay').forEach(overlay => {
                if (overlay.style.display !== 'none') closeModal(overlay);
            });
        }
    });

    // Amount input: format and over-amount warning
    if (amountInput) {
        amountInput.addEventListener('input', () => {
            const val = parseFloat(amountInput.value.replace(/[^0-9.]/g, '')) || 0;
            if (overWarning) {
                overWarning.style.display = val > (config.remainingBalance || 0) && val > 0 ? 'flex' : 'none';
            }
        });
    }

    // Payment method selection active state
    document.querySelectorAll('.mdp-method-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            // CSS handles the visual state via :checked + .mdp-method-card
        });
    });

    // ── File Upload (drag & drop + click) ─────────────────────────
    const dropZone = document.getElementById('receiptDropZone');
    const fileInput = document.getElementById('receiptFile');
    const placeholder = document.getElementById('receiptPlaceholder');
    const preview = document.getElementById('receiptPreview');
    const previewImg = document.getElementById('receiptPreviewImg');
    const removeBtn = document.getElementById('removeReceipt');

    function showFilePreview(file) {
        if (!file) return;
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                if (previewImg) previewImg.src = e.target.result;
                if (placeholder) placeholder.style.display = 'none';
                if (preview) preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            // PDF - show file name
            if (placeholder) {
                placeholder.innerHTML = `
                    <i class="fi fi-rr-file-pdf text-2xl" style="color:#EF4444;"></i>
                    <p class="text-sm font-semibold text-gray-700 mt-2">${file.name}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            }
        }
    }

    function clearFilePreview() {
        if (fileInput) fileInput.value = '';
        if (previewImg) previewImg.src = '';
        if (preview) preview.style.display = 'none';
        if (placeholder) {
            placeholder.style.display = 'flex';
            placeholder.innerHTML = `
                <i class="fi fi-rr-cloud-upload-alt text-2xl" style="color:#EEA24B;"></i>
                <p class="text-sm font-semibold text-gray-500 mt-2">Click or drag to upload</p>
                <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, PDF — max 5MB</p>
            `;
        }
    }

    if (dropZone && fileInput) {
        dropZone.addEventListener('click', e => {
            if (!e.target.closest('.mdp-file-remove')) fileInput.click();
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) showFilePreview(fileInput.files[0]);
        });
        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
            });
        });
        dropZone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                showFilePreview(files[0]);
            }
        });
    }
    if (removeBtn) removeBtn.addEventListener('click', e => { e.stopPropagation(); clearFilePreview(); });

    // ── Payment Form Submission ───────────────────────────────────
    if (paymentForm) {
        paymentForm.addEventListener('submit', async e => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitPaymentBtn');
            const amount = parseFloat((amountInput?.value || '0').replace(/[^0-9.]/g, ''));
            const paymentType = paymentForm.querySelector('input[name="payment_type"]:checked')?.value;
            const txDate = document.getElementById('paymentDate')?.value;

            // Validate
            if (!amount || amount <= 0) {
                shakeElement(amountInput);
                return;
            }
            if (!paymentType) {
                shakeElement(paymentForm.querySelector('.mdp-method-grid'));
                return;
            }
            if (!txDate) {
                shakeElement(document.getElementById('paymentDate'));
                return;
            }

            // Build FormData
            const fd = new FormData();
            fd.append('item_id', config.itemId);
            fd.append('project_id', config.projectId);
            fd.append('amount', amount.toFixed(2));
            fd.append('payment_type', paymentType);
            fd.append('transaction_date', txDate);
            const txNum = document.getElementById('paymentRef')?.value?.trim();
            if (txNum) fd.append('transaction_number', txNum);
            if (fileInput?.files?.length) fd.append('receipt_photo', fileInput.files[0]);

            // Submit
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="mdp-spinner"></span><span>Submitting…</span>';
            }

            try {
                const res = await fetch(config.routes.paymentUpload, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok && (data.success || data.payment)) {
                    closeModal(paymentModal);
                    showResultModal('success', 'Payment Submitted!', data.message || 'Your payment receipt has been uploaded successfully and is pending review.');
                } else {
                    showResultModal('error', 'Upload Failed', data.message || 'Failed to upload payment receipt. Please try again.');
                }
            } catch (err) {
                console.error('Payment upload error:', err);
                showResultModal('error', 'Error', 'A network error occurred. Please check your connection and try again.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fi fi-rr-paper-plane"></i><span>Submit Payment</span>';
                }
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // COMPLETION CONFIRM MODAL
    // ══════════════════════════════════════════════════════════════════
    const completeModal = document.getElementById('completeConfirmModal');
    const openCompleteBtn = document.getElementById('openCompleteModalBtn');
    const closeCompleteBtn = document.getElementById('closeCompleteModal');
    const cancelCompleteBtn = document.getElementById('cancelCompleteBtn');
    const confirmCompleteBtn = document.getElementById('confirmCompleteBtn');

    if (openCompleteBtn) openCompleteBtn.addEventListener('click', () => openModal(completeModal));
    if (closeCompleteBtn) closeCompleteBtn.addEventListener('click', () => closeModal(completeModal));
    if (cancelCompleteBtn) cancelCompleteBtn.addEventListener('click', () => closeModal(completeModal));

    if (confirmCompleteBtn) {
        confirmCompleteBtn.addEventListener('click', async () => {
            confirmCompleteBtn.disabled = true;
            confirmCompleteBtn.innerHTML = '<span class="mdp-spinner"></span><span>Processing…</span>';

            try {
                const res = await fetch(config.routes.milestoneItemComplete, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: config.userId }),
                });
                const data = await res.json();
                if (res.ok && (data.success || data.message)) {
                    closeModal(completeModal);
                    let msg = data.message || 'Milestone item has been marked as complete.';
                    let carryInfo = null;
                    const cf = data.carry_forward || (data.data && data.data.carry_forward);
                    if (cf && cf.shortfall) {
                        carryInfo = `₱${parseFloat(cf.shortfall).toLocaleString(undefined, {minimumFractionDigits: 2})} shortfall has been carried forward to "${cf.carried_to_title || 'the next item'}".`;
                    }
                    showResultModal('success', 'Milestone Completed!', msg, carryInfo);
                } else {
                    showResultModal('error', 'Action Failed', data.message || 'Failed to mark as complete. Please try again.');
                }
            } catch (err) {
                console.error('Complete error:', err);
                showResultModal('error', 'Error', 'A network error occurred. Please try again.');
            } finally {
                confirmCompleteBtn.disabled = false;
                confirmCompleteBtn.innerHTML = '<i class="fi fi-rr-badge-check"></i><span>Yes, Mark Complete</span>';
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // RESULT MODAL
    // ══════════════════════════════════════════════════════════════════
    const resultModal = document.getElementById('resultModal');
    const resultOkBtn = document.getElementById('resultOkBtn');

    function showResultModal(type, title, message, carryForwardText) {
        const icon = document.getElementById('resultIcon');
        const titleEl = document.getElementById('resultTitle');
        const msgEl = document.getElementById('resultMessage');
        const cfCard = document.getElementById('resultCarryForward');
        const cfText = document.getElementById('carryForwardText');

        if (icon) {
            if (type === 'error') {
                icon.className = 'mdp-result-icon error';
                icon.innerHTML = '<i class="fi fi-rr-cross-circle"></i>';
            } else {
                icon.className = 'mdp-result-icon';
                icon.innerHTML = '<i class="fi fi-rr-badge-check"></i>';
            }
        }
        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (cfCard) cfCard.style.display = carryForwardText ? 'block' : 'none';
        if (cfText) cfText.textContent = carryForwardText || '';

        setTimeout(() => openModal(resultModal), 300);
    }

    if (resultOkBtn) {
        resultOkBtn.addEventListener('click', () => {
            closeModal(resultModal);
            // Reload the page to reflect updated state
            setTimeout(() => window.location.reload(), 250);
        });
    }

    // ── Helper: Shake invalid element ─────────────────────────────
    function shakeElement(el) {
        if (!el) return;
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = 'shake 0.4s ease';
        el.style.borderColor = '#EF4444';
        setTimeout(() => {
            el.style.animation = '';
            el.style.borderColor = '';
        }, 1500);
    }

    // ════════════════════════════════════════════════════════════════
    // PROJECT SUMMARY MODAL
    // ════════════════════════════════════════════════════════════════
    const psmModal = document.getElementById('projectSummaryModal');
    const closePsmBtn = document.getElementById('closeProjectSummaryModal');
    const cancelPsmBtn = document.getElementById('cancelProjectSummaryModal');

    // Close handlers
    [closePsmBtn, cancelPsmBtn].forEach(btn => {
        if (btn) btn.addEventListener('click', () => closeModal(psmModal));
    });
    if (psmModal) {
        psmModal.addEventListener('click', e => {
            if (e.target === psmModal) closeModal(psmModal);
        });
    }

    // Collapsible sections – init
    initPsmAccordion();

    function initPsmAccordion() {
        document.querySelectorAll('[data-psm-toggle]').forEach(toggle => {
            const key = toggle.getAttribute('data-psm-toggle');
            const body = document.querySelector(`[data-psm-body="${key}"]`);
            const section = toggle.closest('.psm-section');
            if (!body || !section) return;

            // Remove old listeners by cloning
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);

            if (!body.classList.contains('psm-collapsed')) {
                section.classList.add('expanded');
            }

            newToggle.addEventListener('click', () => {
                const isCollapsed = body.classList.contains('psm-collapsed');
                if (isCollapsed) {
                    body.classList.remove('psm-collapsed');
                    section.classList.add('expanded');
                } else {
                    body.classList.add('psm-collapsed');
                    section.classList.remove('expanded');
                }
            });
        });
    }

    // PSM Refresh
    const refreshPsmBtn = document.getElementById('refreshProjectSummary');
    if (refreshPsmBtn) {
        refreshPsmBtn.addEventListener('click', async () => {
            const config = window.__milestoneProgressConfig || {};
            const projectId = config.projectId;
            const userId = config.userId;
            if (!projectId) return;

            const body = document.getElementById('projectSummaryModalBody');
            if (!body) return;

            refreshPsmBtn.disabled = true;
            const icon = refreshPsmBtn.querySelector('i');
            if (icon) icon.classList.add('psm-spin');

            try {
                const url = `/api/projects/${projectId}/summary${userId ? '?user_id=' + userId : ''}`;
                const res = await fetch(url);
                const json = await res.json();
                const d = json.data || json;
                if (!d || !d.header) throw new Error('Invalid response');

                body.innerHTML = buildPsmHtml(d);

                const tsEl = document.getElementById('psmGeneratedAt');
                if (tsEl && d.generated_at) {
                    const dt = new Date(d.generated_at);
                    tsEl.textContent = 'Report generated ' + dt.toLocaleDateString('en-US', {month:'2-digit',day:'2-digit',year:'numeric'}) + ' ' + dt.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',hour12:true});
                }

                initPsmAccordion();
            } catch (e) {
                console.error('PSM refresh error:', e);
                showToast('Failed to refresh summary', 'error');
            } finally {
                refreshPsmBtn.disabled = false;
                if (icon) icon.classList.remove('psm-spin');
            }
        });
    }

    // ── Build PSM HTML from JSON ──
    function buildPsmHtml(data) {
        const h = data.header || {};
        const o = data.overview || {};
        const milestones = data.milestones || [];
        const budgetHistory = data.budget_history || [];
        const changeHistory = data.change_history || [];
        const payments = data.payments || {records:[], total_approved:0, total_pending:0, total_rejected:0};
        const reports = data.progress_reports || [];

        const fmt = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', {minimumFractionDigits:0});
        const fmt2 = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', {minimumFractionDigits:2});
        const fmtDate = (d) => { if (!d) return '—'; const dt = new Date(d); return dt.toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'}); };
        const fmtDateTime = (d) => { if (!d) return ''; const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'2-digit',day:'2-digit',year:'numeric'}) + ' ' + dt.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',hour12:true}); };
        const sc = (s) => {
            const m = {completed:['#D1FAE5','#10B981'],approved:['#D1FAE5','#10B981'],pending:['#FEF3C7','#F59E0B'],submitted:['#FEF3C7','#F59E0B'],active:['#DBEAFE','#3B82F6'],in_progress:['#DBEAFE','#3B82F6'],rejected:['#FEE2E2','#EF4444'],revision_requested:['#FFF3E6','#EC7E00']};
            return m[(s||'').toLowerCase()] || ['#F1F5F9','#64748B'];
        };
        const badge = (s) => { const c=sc(s); return `<span class="psm-badge" style="background:${c[0]};color:${c[1]};">${(s||'').replace(/_/g,' ')}</span>`; };
        const esc = (s) => { const el = document.createElement('span'); el.textContent = s || ''; return el.innerHTML; };

        const progressPct = o.total_milestones > 0 ? Math.round((o.completed_milestones||0)/o.total_milestones*100) : 0;
        const budgetUtil = o.current_budget > 0 ? Math.round((o.total_paid||0)/o.current_budget*100) : 0;

        let html = '';

        // A. Header Card
        html += `<div class="psm-header-card">
            <h4 class="psm-header-title">${esc(h.project_title)}</h4>
            ${h.project_description ? `<p class="psm-header-desc">${esc(h.project_description)}</p>` : ''}
            <div class="flex items-center justify-between flex-wrap gap-2 mt-3">
                <div class="flex items-center gap-1.5">
                    <i class="fi fi-rr-marker text-xs text-gray-400"></i>
                    <span class="text-xs text-gray-500">${esc(h.project_location||'—')}</span>
                </div>
                ${badge((h.status||'').toUpperCase())}
            </div>
            <div class="psm-divider"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="psm-meta-label">PROPERTY OWNER</p>
                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(h.owner_name||'—')}</p>
                    ${h.owner_email?`<p class="text-xs text-gray-400 mt-0.5">${esc(h.owner_email)}</p>`:''}
                </div>
                <div>
                    <p class="psm-meta-label">CONTRACTOR</p>
                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(h.contractor_name||'—')}</p>
                    ${h.contractor_company?`<p class="text-xs text-gray-400 mt-0.5">${esc(h.contractor_company)}</p>`:''}
                </div>
            </div>
            <div class="psm-divider"></div>
            <div class="flex items-center gap-3 flex-wrap">
                <div><p class="psm-meta-label">START</p><p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${fmtDate(h.original_start_date)}</p></div>
                <i class="fi fi-rr-arrow-right text-xs text-gray-300"></i>
                <div><p class="psm-meta-label">${h.was_extended?'CURRENT END':'END'}</p><p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${fmtDate(h.current_end_date)}</p></div>
                ${h.was_extended && h.original_end_date !== h.current_end_date ? '<span class="psm-badge" style="background:#FEF3C7;color:#F59E0B;"><i class="fi fi-rr-clock" style="font-size:0.5rem;"></i> Extended</span>' : ''}
            </div>
        </div>`;

        // B. Executive Overview
        html += buildPsmSection('overview', 'fi-rr-chart-histogram', 'Executive Overview', false, `
            <div class="mb-3">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Milestone Progress</span>
                    <span class="text-xs font-bold text-gray-900">${progressPct}%</span>
                </div>
                <div class="psm-progress-track"><div class="psm-progress-fill" style="width:${progressPct}%;background:#10B981;"></div></div>
                <p class="text-[0.6875rem] text-gray-400 mt-1">${o.completed_milestones||0} of ${o.total_milestones||0} milestones completed</p>
            </div>
            <div class="mb-4">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Budget Utilization</span>
                    <span class="text-xs font-bold text-gray-900">${budgetUtil}%</span>
                </div>
                <div class="psm-progress-track"><div class="psm-progress-fill" style="width:${Math.min(budgetUtil,100)}%;background:${budgetUtil>100?'#EF4444':'#3B82F6'};"></div></div>
            </div>
            <div class="psm-fin-grid">
                <div class="psm-fin-cell"><span class="psm-fin-label">ORIGINAL BUDGET</span><span class="psm-fin-value">${fmt(o.original_budget)}</span></div>
                <div class="psm-fin-cell ${o.current_budget!==o.original_budget?'psm-fin-highlight':''}"><span class="psm-fin-label">CURRENT BUDGET</span><span class="psm-fin-value">${fmt(o.current_budget)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">TOTAL PAID</span><span class="psm-fin-value" style="color:#10B981;">${fmt(o.total_paid)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">PENDING</span><span class="psm-fin-value" style="color:#F59E0B;">${fmt(o.total_pending)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">REMAINING</span><span class="psm-fin-value">${fmt(o.remaining_balance)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">PAYMENT MODE</span><span class="psm-fin-value-text">${esc((o.payment_mode||'—').replace(/_/g,' '))}</span></div>
            </div>
        `);

        // C. Milestones
        html += buildPsmSection('milestones', 'fi-rr-layers', `Milestones (${milestones.length})`, false,
            milestones.map(m => {
                const mc = sc(m.status);
                return `<div class="psm-milestone-card">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="psm-milestone-seq">${m.sequence_order||''}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-tight" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(m.title)}</p>
                            <p class="text-[0.6875rem] text-gray-400 mt-0.5">${esc(m.milestone_name)}</p>
                        </div>
                        <span class="psm-badge" style="background:${mc[0]};color:${mc[1]};">${(m.status||'').replace(/_/g,' ')}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <div><p class="psm-meta-label">BUDGET</p><p class="text-xs font-semibold text-gray-900">${fmt(m.current_allocation)}</p></div>
                        <div><p class="psm-meta-label">PAID</p><p class="text-xs font-semibold" style="color:#10B981;">${fmt(m.total_paid)}</p></div>
                        <div><p class="psm-meta-label">DUE</p><p class="text-xs font-semibold text-gray-900">${fmtDate(m.current_due_date)}</p></div>
                    </div>
                    ${m.was_extended?`<div class="flex items-center gap-1.5 mb-2"><i class="fi fi-rr-clock text-xs text-amber-500"></i><span class="text-[0.6875rem] text-amber-500 font-medium">Extended ${m.extension_count||0}× (was ${fmtDate(m.original_due_date)})</span></div>`:''}
                    <div class="psm-progress-track" style="height:4px;"><div class="psm-progress-fill" style="width:${m.percentage_progress||0}%;background:#3B82F6;"></div></div>
                </div>`;
            }).join('')
        );

        // D. Budget History
        if (budgetHistory.length > 0) {
            html += buildPsmSection('budget', 'fi-rr-arrow-trend-up', `Budget History (${budgetHistory.length})`, true,
                budgetHistory.map(bh => {
                    const bhc = sc(bh.status);
                    return `<div class="psm-history-row">
                        <div class="psm-history-dot"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${bh.change_type?'Budget '+bh.change_type.charAt(0).toUpperCase()+bh.change_type.slice(1):'Timeline Update'}</span>
                                <span class="psm-badge" style="background:${bhc[0]};color:${bhc[1]};">${bh.status||''}</span>
                            </div>
                            ${bh.previous_budget!=null&&bh.updated_budget!=null?`<p class="text-xs text-gray-500">${fmt(bh.previous_budget)} → ${fmt(bh.updated_budget)}</p>`:''}
                            ${bh.previous_end_date&&bh.proposed_end_date?`<p class="text-xs text-gray-500">${fmtDate(bh.previous_end_date)} → ${fmtDate(bh.proposed_end_date)}</p>`:''}
                            ${bh.reason?`<p class="text-[0.6875rem] text-gray-400 italic mt-1">"${esc(bh.reason)}"</p>`:''}
                            <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDate(bh.date_proposed)}</p>
                        </div>
                    </div>`;
                }).join('')
            );
        }

        // E. Change Log
        if (changeHistory.length > 0) {
            html += buildPsmSection('changelog', 'fi-rr-time-past', `Change Log (${changeHistory.length})`, true,
                changeHistory.map(evt => `<div class="psm-history-row">
                    <div class="psm-history-dot" style="background:#3B82F6;"></div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(evt.action)}</p>
                        ${evt.performed_by?`<p class="text-xs text-gray-500 mt-0.5">by ${esc(evt.performed_by)}</p>`:''}
                        ${evt.notes?`<p class="text-[0.6875rem] text-gray-400 italic mt-1">"${esc(evt.notes)}"</p>`:''}
                        ${evt.reference?`<p class="text-[0.625rem] text-blue-500 mt-0.5">${esc(evt.reference)}</p>`:''}
                        <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDateTime(evt.date)}</p>
                    </div>
                </div>`).join('')
            );
        }

        // F. Payments
        const recs = payments.records || [];
        html += buildPsmSection('payments', 'fi-rr-credit-card', `Payments (${recs.length})`, true, `
            <div class="grid grid-cols-3 gap-2 mb-3">
                <div class="psm-payment-pill" style="border-color:#10B981;"><span class="psm-meta-label" style="color:#10B981;">APPROVED</span><span class="text-sm font-bold" style="color:#10B981;">${fmt(payments.total_approved)}</span></div>
                <div class="psm-payment-pill" style="border-color:#F59E0B;"><span class="psm-meta-label" style="color:#F59E0B;">PENDING</span><span class="text-sm font-bold" style="color:#F59E0B;">${fmt(payments.total_pending)}</span></div>
                <div class="psm-payment-pill" style="border-color:#EF4444;"><span class="psm-meta-label" style="color:#EF4444;">REJECTED</span><span class="text-sm font-bold" style="color:#EF4444;">${fmt(payments.total_rejected)}</span></div>
            </div>
            ${recs.length===0?'<p class="text-xs text-gray-400 italic py-3">No payment records yet.</p>':
            recs.map(p => { const pc=sc(p.status); return `<div class="psm-payment-row">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(p.milestone)}</p>
                        <p class="text-[0.6875rem] text-gray-400 capitalize">${(p.payment_type||'').replace(/_/g,' ')}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900">${fmt2(p.amount)}</p>
                        <span class="psm-badge mt-0.5" style="background:${pc[0]};color:${pc[1]};">${p.status||''}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    ${p.transaction_number?`<span class="text-[0.625rem] text-gray-400">Ref: ${esc(p.transaction_number)}</span>`:'<span></span>'}
                    <span class="text-[0.625rem] text-gray-300">${fmtDate(p.transaction_date)}</span>
                </div>
            </div>`; }).join('')}
        `);

        // G. Progress Reports
        if (reports.length > 0) {
            html += buildPsmSection('progress', 'fi-rr-document', `Progress Reports (${reports.length})`, true,
                reports.map(rp => { const rc=sc(rp.status); return `<div class="psm-report-row">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(rp.report_title||'Progress Report')}</p>
                        <p class="text-[0.6875rem] text-gray-400 mt-0.5">${esc(rp.milestone||'')}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="psm-badge" style="background:${rc[0]};color:${rc[1]};">${(rp.status||'').replace(/_/g,' ')}</span>
                        <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDate(rp.submitted_at)}</p>
                    </div>
                </div>`; }).join('')
            );
        }

        html += `<p class="text-center text-[0.625rem] text-gray-300 mt-4 pb-2" id="psmGeneratedAt">Report generated ${fmtDateTime(data.generated_at)}</p>`;
        return html;
    }

    function buildPsmSection(key, icon, title, collapsed, content) {
        return `<div class="psm-section" data-psm-section="${key}">
            <button class="psm-section-toggle" data-psm-toggle="${key}" type="button">
                <div class="flex items-center gap-2">
                    <i class="fi ${icon} text-sm" style="color:#EEA24B;"></i>
                    <span class="psm-section-title">${title}</span>
                </div>
                <i class="fi fi-rr-angle-small-down psm-chevron" style="${collapsed?'transform:rotate(-90deg)':''}"></i>
            </button>
            <div class="psm-section-body${collapsed?' psm-collapsed':''}" data-psm-body="${key}">${content}</div>
        </div>`;
    }
});
