<?php
/**
 * Footer Template
 * Customer & Real-Time Trading Management System
 */
?>
    <?php if (isLoggedIn()): ?>
    </main>
    <?php endif; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        // Confirm Delete
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }
        
        // Auto calculate totals for deal items
        function calculateItemTotal(row) {
            const buyQty = parseFloat(row.querySelector('.buy-qty').value) || 0;
            const buyPrice = parseFloat(row.querySelector('.buy-price').value) || 0;
            const sellQty = parseFloat(row.querySelector('.sell-qty').value) || 0;
            const sellPrice = parseFloat(row.querySelector('.sell-price').value) || 0;
            
            const totalBuy = buyQty * buyPrice;
            const totalSell = sellQty * sellPrice;
            const profit = totalSell - totalBuy;
            
            row.querySelector('.total-buy').value = totalBuy.toFixed(2);
            row.querySelector('.total-sell').value = totalSell.toFixed(2);
            row.querySelector('.item-profit').value = profit.toFixed(2);
            
            calculateDealTotals();
        }
        
        // Calculate deal totals
        function calculateDealTotals() {
            let totalBuy = 0;
            let totalSell = 0;
            
            document.querySelectorAll('.deal-item-row').forEach(row => {
                totalBuy += parseFloat(row.querySelector('.total-buy').value) || 0;
                totalSell += parseFloat(row.querySelector('.total-sell').value) || 0;
            });
            
            const profit = totalSell - totalBuy;
            
            if (document.getElementById('grandTotalBuy')) {
                document.getElementById('grandTotalBuy').textContent = '$' + totalBuy.toFixed(2);
            }
            if (document.getElementById('grandTotalSell')) {
                document.getElementById('grandTotalSell').textContent = '$' + totalSell.toFixed(2);
            }
            if (document.getElementById('grandProfit')) {
                document.getElementById('grandProfit').textContent = '$' + profit.toFixed(2);
            }
        }
        
        // Add new deal item row
        function addDealItemRow() {
            const container = document.getElementById('dealItemsContainer');
            const template = document.getElementById('dealItemTemplate');
            if (container && template) {
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);
            }
        }
        
        // Remove deal item row
        function removeDealItemRow(btn) {
            const rows = document.querySelectorAll('.deal-item-row');
            if (rows.length > 1) {
                btn.closest('.deal-item-row').remove();
                calculateDealTotals();
            } else {
                alert('At least one item is required');
            }
        }
    </script>
    
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
