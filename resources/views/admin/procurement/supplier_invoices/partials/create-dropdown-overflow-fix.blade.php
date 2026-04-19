@once
    @push('styles')
        <style>
            #procurement-create-form .card,
            #procurement-create-form .card-body,
            #procurement-create-form #procurement-line-items,
            #procurement-create-form [data-line-item],
            #procurement-create-form .position-relative {
                overflow: visible !important;
            }

            #procurement-create-form [data-product-results],
            #procurement-create-form [data-supplier-results] {
                z-index: 1085 !important;
                max-height: min(18rem, 45vh);
                overflow-y: auto;
                overscroll-behavior: contain;
            }
        </style>
    @endpush
@endonce
