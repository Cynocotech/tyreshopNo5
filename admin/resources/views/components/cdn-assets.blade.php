{{-- CDN-based assets: no Vite/npm build required. Use in layouts instead of @vite(). --}}
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
<style>
/* Booking modal buttons - match app.css */
#booking-modal #booking-modal-actions a.btn-call { display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;border-radius:0.5rem;font-weight:600;font-size:0.875rem;text-decoration:none;border:none;background-color:#16a34a !important;color:#fff !important; }
#booking-modal #booking-modal-actions a.btn-call:hover { background-color:#15803d !important; }
#booking-modal #booking-modal-actions a.btn-email { display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;border-radius:0.5rem;font-weight:600;font-size:0.875rem;text-decoration:none;border:none;background-color:#475569 !important;color:#fff !important; }
#booking-modal #booking-modal-actions a.btn-email:hover { background-color:#334155 !important; }

/* Admin form inputs - proper borders, padding, focus states */
.admin-form-input,
main input[type="text"],
main input[type="email"],
main input[type="url"],
main input[type="number"],
main input[type="password"],
main input[type="date"],
main select,
main textarea {
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  line-height: 1.5;
}
main input:focus,
main select:focus,
main textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
</style>
