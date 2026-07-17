import './bootstrap';
import collapse from '@alpinejs/collapse';

// Livewire gestiona Alpine automáticamente en todas las páginas (inject_assets = true).
// Solo registramos el plugin de collapse antes de que Alpine arranque.
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});


