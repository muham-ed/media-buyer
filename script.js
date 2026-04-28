// script.js
// رسم المخطط البياني باستخدام Chart.js
if (typeof chartData !== 'undefined') {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'الإنفاق اليومي ($)',
                data: chartData.spends,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
            }
        }
    });
}

// تأكيد قبل إرسال نموذج إنشاء الحملة
document.getElementById('aiCampaignForm')?.addEventListener('submit', function(e) {
    if(!confirm('سيتم إنشاء الحملة باستخدام Claude API وقد تستهلك ميزانية حقيقية. هل أنت متأكد؟')) {
        e.preventDefault();
    }
});