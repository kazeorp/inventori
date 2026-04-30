<style>
    /* Virtual Keyboard Styles */
    #virtual-keyboard {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #2c3e50;
        padding: 10px;
        z-index: 3000; /* Higher than Bootstrap modals */
        display: none;
        flex-direction: column;
        gap: 5px;
        box-shadow: 0 -5px 25px rgba(0,0,0,0.4);
        border-top: 4px solid #0d6efd;
    }
    .keyboard-row { display: flex; justify-content: center; gap: 5px; width: 100%; }
    .key {
        background: #fff; border: 1px solid #ccc; border-radius: 6px;
        padding: 15px 0; min-width: 35px; font-size: 1.2rem; font-weight: bold;
        flex: 1; max-width: 60px; touch-action: manipulation; user-select: none;
        box-shadow: 0 3px 0 #bdc3c7;
        color: #333;
    }
    .key:active { transform: translateY(3px); box-shadow: none; background: #ecf0f1; }
    .key.special { background: #7f8c8d; color: white; max-width: 100px; box-shadow: 0 3px 0 #34495e; }
    .key.enter { background: #198754; color: white; max-width: 140px; box-shadow: 0 3px 0 #0f5132; }
</style>

<div id="virtual-keyboard">
    <div class="keyboard-row">
        <?php foreach (['1','2','3','4','5','6','7','8','9','0'] as $k) {
            echo "<button type='button' class='key'>$k</button>";
        } ?>
        <button type="button" class="key special" data-action="backspace"><i class="bi bi-backspace"></i></button>
    </div>
    <div class="keyboard-row">
        <?php foreach (['Q','W','E','R','T','Y','U','I','O','P'] as $k) {
            echo "<button type='button' class='key'>$k</button>";
        } ?>
    </div>
    <div class="keyboard-row">
        <?php foreach (['A','S','D','F','G','H','J','K','L'] as $k) {
            echo "<button type='button' class='key'>$k</button>";
        } ?>
        <button type="button" class="key">-</button>
    </div>
    <div class="keyboard-row">
        <?php foreach (['Z','X','C','V','B','N','M'] as $k) {
            echo "<button type='button' class='key'>$k</button>";
        } ?>
        <button type="button" class="key">.</button>
        <button type="button" class="key enter" data-action="enter">ENTER</button>
        <button type="button" class="key special" data-action="close"><i class="bi bi-keyboard-hide"></i></button>
    </div>
</div>

<script>
(function() {
    let currentInput = null;
    const kb = document.getElementById('virtual-keyboard');

    // Automatically show keyboard for any focused text input or textarea
    document.addEventListener('focusin', (e) => {
        const tag = e.target.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA') {
            currentInput = e.target;
            kb.style.display = "flex";
        }
    });

    document.querySelectorAll('#virtual-keyboard .key').forEach(btn => {
        btn.addEventListener('mousedown', (e) => {
            e.preventDefault(); // Prevent input from losing focus
            if (!currentInput) return;

            const action = btn.getAttribute('data-action');
            if (action === 'backspace') {
                currentInput.value = currentInput.value.slice(0, -1);
            } else if (action === 'enter') {
                // If using the main scan input, trigger the specific logic
                if (currentInput.id === 'manual-hostname-input' && typeof window.handleAssetCheck === 'function') {
                    window.handleAssetCheck(currentInput.value);
                } else {
                    // Normal behavior: close keyboard
                    kb.style.display = 'none';
                    currentInput.blur();
                }
            } else if (action === 'close') {
                kb.style.display = 'none';
                currentInput.blur();
            } else if (!action) {
                currentInput.value += btn.innerText;
            }

            // Manually trigger input events for other JS logic that might be listening
            currentInput.dispatchEvent(new Event('input', { bubbles: true }));
            currentInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // Close keyboard when clicking outside of an input or the keyboard itself
    document.addEventListener('mousedown', (e) => {
        if (kb.style.display === 'flex' &&
            !kb.contains(e.target) &&
            e.target !== currentInput) {
            kb.style.display = 'none';
        }
    });
})();
</script>