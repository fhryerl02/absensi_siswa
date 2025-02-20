<?php
function show_notification($message, $type = 'success') {
    $bg_color = $type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
    
    echo "<div class='notification fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg border-l-4 {$bg_color} transform translate-x-0 transition-transform duration-300 ease-in-out z-50' role='alert'>
            <div class='flex items-center'>
                <div class='py-1'>
                    <svg class='mr-4 h-6 w-6' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        " . ($type === 'success' ? 
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' : 
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />') . "
                    </svg>
                </div>
                <div>
                    <p class='font-bold'>{$message}</p>
                </div>
                <button class='ml-6 focus:outline-none' onclick='this.parentElement.parentElement.remove();'>
                    <svg class='h-4 w-4' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' />
                    </svg>
                </button>
            </div>
          </div>
          <script>
            setTimeout(() => {
                const notification = document.querySelector('.notification');
                if (notification) {
                    notification.style.transform = 'translateX(150%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
          </script>";
}
?>
