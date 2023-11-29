<?php


/**
 * Go Free Delivery Cart Widget module for PrestaShop
 *
 * @author GoGroup
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class go_freedelivery extends Module
{


    /**
     * Configuration errors
     *
     */
    private array $configurationErrors = [];

    /**
     * Constructs a new instance of the class.
     *
     */
    public function __construct()
    {
        $this->name = 'go_freedelivery';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'GoGroup';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Go Free Delivery Cart Widget');
        $this->description = $this->l('Go Free Delivery Cart Widget');


        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    }

    /**
     * Module installation
     */
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayShoppingCartFooter') ||
            !$this->registerHook('actionFrontControllerSetMedia')
        ) {
            return false;
        }
        Configuration::updateValue('GO_FREE_DELIVERY_MIN_VALUE', 0);
        Configuration::updateValue('GO_FREE_DELIVERY_CATEGORIES', '');



        return true;

    }

    /**
     * Module uninstallation
     */
    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    /**
     * Configuration page in admin panel
     * @throws Exception
     */
    public function getContent(): string
    {
        if (Tools::isSubmit('submitForm')) {
            $this->processForm();
        }

        return $this->displayForm();

    }


    /**
     * Display configuration form
     * @throws Exception
     */
    private function displayForm(): string
    {


        $this->context->smarty->assign([
            'form' => $this->getConfigForm(),
            'errors' => $this->configurationErrors,
            'min_order_value' => Configuration::get('GO_FREE_DELIVERY_MIN_VALUE'),
            'categories' => $this->getCategoriesNames()
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');

    }

    /**
     * Generates the function comment for the getConfigForm() method.
     *
     * @return string The generated form.
     */
    protected function getConfigForm(): string
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Go Free Delivery Cart Widget'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Min Order Value'),
                        'name' => 'min_order_value',
                        'required' => true,
                        'desc' => $this->l('Min Order Value for free delivery'),
                    ],
                    'input' => [
                        'type' => 'text',
                        'label' => $this->l('Categories'),
                        'name' => 'categories',
                        'desc' => $this->l('Categories inserted by comma'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),

                ],
            ],
        ];

        $categories = unserialize(Configuration::get('GO_FREE_DELIVERY_CATEGORIES'), ['allowed_classes' => false]);


        $formHelper = new HelperForm();
        $formHelper->submit_action = 'submitForm';
        $formHelper->tpl_vars = [
            'fields_value' => [
                'min_order_value' => Tools::getValue('min_order_value', Configuration::get('GO_FREE_DELIVERY_MIN_VALUE')),
                'categories' => $this->arrayToString($categories),
            ],
        ];

        return $formHelper->generateForm([$form]);
    }

    //hook for displaying widget in cart
    public function hookDisplayShoppingCartFooter()
    {


        $idShop = $this->context->shop->id;
        $idLang = $this->context->language->id;
        $categories = unserialize(Configuration::get('GO_FREE_DELIVERY_CATEGORIES'), ['allowed_classes' => false]);

        $result = $this->getProductToFreeDelivery($idShop, $idLang, $categories);
        
        if (empty($result)) {
            $this->context->smarty->assign(['viewProductToFree' => 0]);
        }

            return $this->display(__FILE__, 'views/templates/hook/cart.tpl');
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/main.js');

        Media::addJsDef([
            'GoWidgetUrl' => $this->context->link->getModuleLink('go_freedelivery', 'widget', []),
        ]);


    }


    private function validateMinOrderValue($getValue)
    {
        if (!is_numeric($getValue)) {
            $this->configurationErrors[] = $this->l('Min Order Value must be a number');
            return;
        }

        if ($getValue < 0) {
            $this->configurationErrors[] = $this->l('Min Order Value must be greater than 0');
            return;

        }


    }

    private function getProductToFreeDelivery($idShop, $idLang, $categories, $productsLimit = 24)
    {

        try {
            $total = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);

            if ($total == 0)
                return array();

            $toFree = $this->getFreeDeliveryThreshold($total);

            $idShopGroup = $this->context->shop->id_shop_group;

            if ($toFree <= 0) {
                return [];
            }


            $categoryCondition = $this->getCategoryCondition($categories);

            $result = $this->queryExecution($idShop, $idLang, $idShopGroup, $toFree, $categoryCondition, $productsLimit);


            $res = array();
            foreach ($result as $item) {
                $res[$item['id_product']] = $item;
            }


            if (!empty($result))
                $this->context->smarty->assign(['products' => Product::getProductsProperties((int)$this->context->language->id, $res), 'viewProductToFree' => 1]);

            return $result;
        } catch (Exception $e) {
            PrestaShopLoggerCore::addLog($e->getMessage(), 2, null, 'Go Free Delivery Cart Widget', 1);
            return [];
        }
    }

    /**
     * Parses the given value into an array of categories.
     *
     * @param string $getValue The value to be parsed.
     * @return string The serialized array of categories.
     */
    private function parseCategories(string $getValue): string
    {
        if (empty($getValue)) {
            return '';
        }

        $categories = explode(',', $getValue);

        return serialize($categories);
    }

    /**
     * Retrieves the name of the product category.
     *
     * @param int $category The ID of the category.
     * @return string The name of the product category.
     * @throws Exception If the category does not exist.
     */
    private function getProductCategoryName(int $category): string
    {
        $category = new Category($category);

        return $category->getName();
    }

    /**
     * Converts an array to a string by concatenating its elements with a comma separator.
     *
     * @param mixed $getValue The value to be converted. It can be an array or any other datatype.
     * @return string The converted string value.
     */
    private function arrayToString($getValue): string
    {
        if (is_array($getValue)) {
            return implode(',', $getValue);
        }

        return $getValue;
    }

    /**
     * Process configuration form
     */
    public function processForm(): void
    {
        $this->validateMinOrderValue(Tools::getValue('min_order_value'));
        Configuration::updateValue('GO_FREE_DELIVERY_MIN_VALUE', Tools::getValue('min_order_value'));

        $categories = $this->parseCategories(Tools::getValue('categories'));
        Configuration::updateValue('GO_FREE_DELIVERY_CATEGORIES', $categories);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getCategoriesNames(): array
    {
        $categories = unserialize(Configuration::get('GO_FREE_DELIVERY_CATEGORIES'), ['allowed_classes' => false]);

        if (empty($categories)) {
            return [];
        }

        $categoriesNames = [];

        foreach ($categories as $category) {
            $categoriesNames[] = $this->getProductCategoryName((int)$category);
        }
        return $categoriesNames;
    }

    /**
     * @return float
     */
    public function getFreeDeliveryThreshold($total): float
    {

        $orderTotalThreshold = Configuration::get('GO_FREE_DELIVERY_MIN_VALUE');
        return $orderTotalThreshold - $total;
    }

    /**
     * @param $categories
     * @return string
     */
    public function getCategoryCondition($categories): string
    {
        $categoryCondition = '';
        if (!empty($categories) && is_array($categories)) {
            $categoryList = implode(',', array_map('intval', $categories));
            $categoryCondition = ' AND cp.id_category IN (' . $categoryList . ')';
        }
        return $categoryCondition;
    }

    /**
     * @param int $idShop
     * @param int $idLang
     * @param $idShopGroup
     * @param float $toFree
     * @param string $categoryCondition
     * @param int $productsLimit
     * @return array|bool
     */
    public function queryExecution(int $idShop, int $idLang, $idShopGroup, float $toFree, string $categoryCondition, int $productsLimit)
    {
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`,
            pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, image.`id_image` id_image, image.`id_image` as id_image2, il.`legend`, m.`name` AS manufacturer_name
            FROM `' . _DB_PREFIX_ . 'product` p
            INNER JOIN ' . _DB_PREFIX_ . 'product_shop product_shop ON (product_shop.id_product = p.id_product AND product_shop.id_shop = ' . (int)$idShop . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` `pl` ON p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int)$idLang . '
            JOIN ' . _DB_PREFIX_ . 'image image ON p.id_product = image.id_product
            JOIN ' . _DB_PREFIX_ . 'category_product cp ON p.id_product=cp.id_product
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` `il` ON image.`id_image` = il.`id_image`
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` `m` ON m.`id_manufacturer` = p.`id_manufacturer`
            LEFT JOIN ' . _DB_PREFIX_ . 'stock_available stock ON (stock.id_product = p.id_product AND stock.id_product_attribute = 0 AND stock.id_shop = ' . (int)$idShop . ' AND stock.id_shop_group = ' . (int)$idShopGroup . ')
            WHERE p.active = 1 AND ((p.price * 1.23) > ' . (float)$toFree . ' ' . $categoryCondition . ')
            ORDER BY p.price ASC LIMIT ' . (int)$productsLimit;
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }

}

