<style>
.select2-container--bootstrap-5 {
    width: 100% !important;
}
.sticky-top {
    top: 0;
    z-index: 1;
    background-color: #fff;
    border-bottom: 1px solid #dee2e6;
}
.scan-input:disabled {
    background-color: #e9ecef;
}
.scan-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    min-width: 250px;
    max-width: 350px;
}
@media (max-width: 768px) {
    .scan-container {
        padding: 10px;
    }
    .scan-notification {
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
</style>
<?php 
        // Pastikan jQuery terdaftar
        Yii::app()->clientScript->registerCoreScript('jquery');
?>

<div class="scan-container">
    <div class="scan-header">
        <h4>Good Issue Scan</h4>
        <div class="row">
            <div class="col-6">
                <small>Today's Scan: <span id="todayCount">0</span></small>
            </div>
            <div class="col-6 text-end">
                <small>Status: <span id="scanStatus" class="text-success">Ready</span></small>
            </div>
        </div>
    </div>

    <!-- Document Selection -->
    <div class="mb-3">
        <label for="docSalesOrder" class="form-label">Select Sales Order</label>
        <select class="form-control" id="docSalesOrder">
            <option value="">Search Sales Order number...</option>
        </select>
    </div>

    <!-- Customer Info Card -->
    <div id="customerInfo" class="card mb-3" style="display: none;">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-sm-6">
                    <small class="text-muted d-block">Customer Name</small>
                    <strong id="customerName">-</strong>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Customer Address</small>
                    <strong id="customerAddress">-</strong>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">SO Date</small>
                    <span id="soDate">-</span>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Notes</small>
                    <span id="soNotes">-</span>
                </div>
                <div class="col-sm-12 text-end">
                    <button id="btnCreateGI" class="btn btn-primary btn-sm d-none">
                        <i class="fas fa-truck"></i> Create Good Issue
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Detail -->
    <div id="itemsDetail" class="card mb-3" style="display: none;">
        <div class="card-header">
            <h6 class="mb-0">Items to Deliver</h6>
        </div>
        <div class="table-responsive" style="max-height: 300px;">
            <table class="table table-hover table-sm mb-0">
                <thead class="sticky-top bg-light">
                    <tr>
                        <th>Item Name</th>
                        <th class="text-center">SO Qty</th>
                        <th class="text-center">Scanned Qty</th>
                    </tr>
                </thead>
                <tbody id="itemsList">
                    <!-- Items will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scan Input -->
    <div class="form-group mb-3">
        <input type="text" class="form-control scan-input" 
               id="barcodeInput" placeholder="Scan Serial Number/Barcode" 
               autocomplete="off" disabled>
    </div>

    <!-- Recent Scans -->
    <div class="scan-history">
        <h6>Recent Scans</h6>
        <div id="scanHistoryList"></div>
    </div>

    <!-- Notification -->
    <div class="scan-notification" id="scanNotification"></div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true" aria-labelledby="processingModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processingModalLabel">Processing...</h5>
            </div>
            <div class="modal-body">
                Please wait while we process your request.
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Initialize Select2 for Sales Order selection
    $('#docSalesOrder').select2({
        ajax: {
            url: '<?= $this->createUrl("scangi/getSalesOrders"); ?>', // Adjust URL as needed
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Silahkan ketik No SO atau Nama Customer...',
        minimumInputLength: 3,
    });

    // Handle Sales Order selection
    $('#docSalesOrder').on('change', function() {
        const soId = $(this).val();
        if (soId) {
            fetchSalesOrderDetails(soId);
            $('#barcodeInput').prop('disabled', false).focus();
        } else {
            resetView();
        }
    });

    function fetchSalesOrderDetails(soId) {
        $.ajax({
            url: '<?= $this->createUrl("scangi/getDetails"); ?>',
            type: 'GET',
            data: { id: soId },
            success: function(response) {
                if (response.success) {
                    displayCustomerInfo(response.data);
                    displayItems(response.data.items);
                    displayCreateGIButton(response.check);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Failed to fetch Sales Order details');
            }
        });
    }

    function displayCustomerInfo(data) {
        $('#customerInfo').show();
        $('#customerName').text(data.customer_name);
        $('#customerAddress').text(data.customer_address);
        $('#soDate').text(data.so_date);
        $('#soNotes').text(data.notes || '-');
    }

    function displayItems(items) {
        $('#itemsDetail').show();
        $('#itemsList').empty();
        
        items.forEach(function(item, key) {
            $('#itemsList').append(`
                <tr data-item-id="${item.id}">
                    <td>${item.name}</td>
                    <td class="text-center">${new Intl.NumberFormat().format(item.so_qty)}</td>
                    <td class="text-center">
                        <span class="scanned-qty">${new Intl.NumberFormat().format(item.scanned_qty) || 0}</span>
                        <small class="text-muted">/ ${new Intl.NumberFormat().format(item.so_qty)}</small>
                    </td>
                </tr>
            `);
        });
    }

    function displayCreateGIButton(isDisplay) {
        $('#btnCreateGI').toggleClass('d-none', !isDisplay);
    }

    // Handle barcode scanning
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            const soId = $('#docSalesOrder').val();
            
            if (barcode && soId) {
                processScan(soId, barcode);
            }
        }
    });

    // Handle Create Good Issue button
    $('#btnCreateGI').on('click', function() {
        const soId = $('#docSalesOrder').val();
        
        if (!soId) return showNotification('error', 'Please select a Sales Order', 'danger');   
        createGoodIssue(soId);
    });
    
    function processScan(soId, barcode) {
        $.ajax({
            url: '<?= $this->createUrl("goodissue/processScan"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                so_id: soId,
                barcode: barcode,
                '<?php echo Yii::app()->request->csrfTokenName; ?>': '<?php echo Yii::app()->request->csrfToken; ?>'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Scan successful');
                    updateItemQuantity(response.item_id, response.scanned_qty);
                    addToHistory(response.scan);
                    playSound('success');
                } else {
                    showNotification('error', response.message);
                    playSound('error');
                }
            },
            error: function() {
                showNotification('error', 'System error occurred');
                playSound('error');
            },
            complete: function() {
                $('#barcodeInput').val('').focus();
            }
        });
    }

    function createGoodIssue(soId) {
        const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
        processingModal.show();

        $.ajax({
            url: '<?php echo $this->createUrl("goodissue/create"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                id: soId,
                '<?php echo Yii::app()->request->csrfTokenName; ?>': '<?php echo Yii::app()->request->csrfToken; ?>'
            },
            success: function(response) {
                let type = response.status === 'success' ? 'success' : 'error';
                processingModal.hide();

                showNotification(type, response.message);
                
                if (response.status === 'success') {
                    $('#docSalesOrder').val('');
                    $('#customerInfo').hide();
                    $('#itemsDetail').hide();
                    displayCreateGIButton(false);
                }
            },
            error: function(xhr, status, err) {
                processingModal.hide();
                showNotification('error', 'Failed to create Good Issue', status);
            }
        });
    }

    // Utility functions (similar to transfer-in.php)
    function updateItemQuantity(itemId, newQty) {
        $(`tr[data-item-id="${itemId}"] .scanned-qty`).text(newQty);
    }

    function addToHistory(scan) {
        const historyItem = `
            <div class="scan-item mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${scan.barcode} - ${scan.item_name}</strong><br>
                        <small class="text-muted">Scanned at: ${scan.timestamp}</small>
                    </div>
                    <span class="badge bg-success">Success</span>
                </div>
            </div>
        `;
        $('#scanHistoryList').prepend(historyItem);
    }

    function showNotification(type, message) {
        const notif = $('#scanNotification');
        notif.removeClass('alert-success alert-danger')
            .addClass('alert alert-' + (type === 'success' ? 'success' : 'danger'))
            .html(message)
            .show();

        setTimeout(function() {
            notif.fadeOut();
        }, 3000);
    }

    function playSound(type) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        if (type === 'success') {
            oscillator.frequency.setValueAtTime(1500, audioContext.currentTime);
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.5, audioContext.currentTime);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } else {
            oscillator.frequency.setValueAtTime(250, audioContext.currentTime);
            oscillator.type = 'square';
            gainNode.gain.setValueAtTime(0.5, audioContext.currentTime);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        }

        if ('vibrate' in navigator) {
            navigator.vibrate(type === 'success' ? 100 : [100, 100, 100]);
        }
    }

    function resetView() {
        $('#customerInfo, #itemsDetail').hide();
        $('#itemsList').empty();
        $('#barcodeInput').prop('disabled', true);
        $('#scanHistoryList').empty();
    }
});
</script>