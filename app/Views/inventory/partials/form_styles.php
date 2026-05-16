<style>
.inventory-create-form-section {
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.inventory-create-form-section:nth-of-type(1) { background: #f0fdf4; border-color: #bbf7d0; }
.inventory-create-form-section:nth-of-type(2) { background: #fffbeb; border-color: #fde68a; }
.inventory-create-form-section:nth-of-type(3) { background: #eef2ff; border-color: #c7d2fe; }
.inventory-create-form-section:nth-of-type(4) { background: #fff7ed; border-color: #fed7aa; }
.inventory-create-form-section:nth-of-type(5) { background: #f5f3ff; border-color: #ddd6fe; }

.inventory-create-section-title {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.875rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    font-weight: 700;
    font-size: 0.9375rem;
}
.inventory-create-section-title i {
    display: inline-flex;
    height: 2.25rem;
    width: 2.25rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.625rem;
}

[dir="rtl"] .inventory-create-section-title i { margin-right: 0; margin-left: 0.625rem; }

.inventory-create-form-field {
    width: 100%;
    height: 2.75rem;
    padding: 0 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #1e293b;
    font-family: inherit;
}
.inventory-create-form-field:hover { border-color: #9ca3af; }
.inventory-create-form-field:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.inventory-create-form-field::placeholder { color: #9ca3af; }
[dir="rtl"] .inventory-create-form-field { text-align: right; }

.inventory-create-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #1e293b;
    font-family: inherit;
    resize: vertical;
}
.inventory-create-textarea:hover { border-color: #9ca3af; }
.inventory-create-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.inventory-create-textarea::placeholder { color: #9ca3af; }
[dir="rtl"] .inventory-create-textarea { text-align: right; }

.inventory-create-select {
    width: 100%;
    height: 2.75rem;
    padding: 0 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #1e293b;
    font-family: inherit;
    appearance: none;
    cursor: pointer;
}
.inventory-create-select:hover { border-color: #9ca3af; }
.inventory-create-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

.inventory-create-quick-link {
    color: #4f46e5;
    font-size: 0.8125rem;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-family: inherit;
}
.inventory-create-quick-link:hover { color: #3730a3; text-decoration: underline; }

.inventory-create-section-title .icon-emerald { background: #d1fae5; color: #059669; }
.inventory-create-section-title .icon-amber  { background: #fef3c7; color: #d97706; }
.inventory-create-section-title .icon-indigo { background: #e0e7ff; color: #4f46e5; }
.inventory-create-section-title .icon-orange { background: #ffedd5; color: #ea580c; }
.inventory-create-section-title .icon-violet { background: #ede9fe; color: #7c3aed; }
</style>
