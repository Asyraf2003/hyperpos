<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Product;

final class Product
{
    use ProductState;
    use ProductValidation;
    use ProductFactory;
    use ProductMutation;
}
