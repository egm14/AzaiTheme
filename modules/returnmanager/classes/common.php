<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *
 *
 * Description
 *
 * Updates quantity in the cart
 */

class Common extends Module
{

    const TEMPLATE_NAME = 'velsof_rm';
    const ITEM_PER_PAGE = 10;

    /*
     * use "left" to display pagination on left side
     */
    const PAGINATION_ALIGN = 'right';
    const RETURN_SLIP_NAME = 'ReturnSlip';

    public function __construct()
    {
        $this->name = 'returnmanager';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();
    }

    protected function getTemplateDir()
    {
        $iso = Configuration::get('VELSOF_RETURN_MANAGER_DEFAULT_TEMPLATE_LANG');
        return _PS_MODULE_DIR_ . 'returnmanager/mails/' . $iso . '/';
    }

    protected function getReturnSlipName($return_id)
    {
        $query = 'Select id_lang from '._DB_PREFIX_.'velsof_rm_order where id_rm_order = '.(int)$return_id;
        $language_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        $language = Language::getIsoById($language_id);
        if (!$language) {
            $language = Language::getIsoById($this->context->language->id);
        }
        $slipname = $this->getModuleTranslationByLanguage('returnmanager', 'ReturnSlip', 'common', $language);
        return $slipname . $return_id . '.pdf';
    }

    protected function getReturnSlipPath()
    {
        return _PS_MODULE_DIR_ . 'returnmanager/reports/slips/';
    }

    protected function customPaginator($total_records, $total_pages, $ajaxcallfn = '', $active = 0, $current_page = 1)
    {
        $summary_txt = '';
        $pagination = '';
        if ($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages) {
            $summary_align = 'rm-pagination-left';
            $pagination_align = 'rm-pagination-left';
            if (self::PAGINATION_ALIGN == 'right') {
                $summary_align = 'rm-pagination-left';
                $pagination_align = 'rm-pagination-right';
            }
            $record_start = $current_page;
            $record_end = self::ITEM_PER_PAGE;
            if ($current_page > 1) {
                $record_start = (($current_page - 1) * self::ITEM_PER_PAGE) + 1;
                if ($current_page == $total_pages) {
                    $record_end = $total_records;
                } else {
                    $record_end = $current_page * self::ITEM_PER_PAGE;
                }
            }

            $summary_txt = '<div class="' . $summary_align . ' rm-paginate-summary">
				Showing ' . $record_start . ' to ' . $record_end . ' of ' . $total_records .
                ' (' . $total_pages . ' pages)</div>';

            $pagination .= '<div class="' . $pagination_align . '"><ul class="rm-pagination">';

            $ajax_call_function = '';
            if ($ajaxcallfn != '') {
                $ajax_call_function .= $ajaxcallfn . '({page_number}, ' . $active . ');';
            }

            $right_links = $current_page + 3;
            $previous = $current_page - 3; //previous link
            $first_link = true; //boolean var to decide our first link

            if ($current_page > 1) {
                $previous_link = ($previous == 0) ? 1 : $previous;
                $pagination .= '<li class="first"><a href="javascript:void(0)" data-page="1"
					onclick="' . str_replace('{page_number}', 1, $ajax_call_function) . '"
					title="First">&laquo;</a></li>'; //first link
                $pagination .= '<li><a href="javascript:void(0)" data-page="' . $previous_link . '"
					onclick="' . str_replace('{page_number}', $previous_link, $ajax_call_function) . '"
					title="Previous">&lt;</a></li>'; //previous link
                for ($i = ($current_page - 2); $i < $current_page; $i++) {
                    if ($i > 0) {
                        $pagination .= '<li><a href="javascript:void(0)" data-page="' . $i . '"
						onclick="' . str_replace('{page_number}', $i, $ajax_call_function) . '"
						title="Page' . $i . '">' . $i . '</a></li>';
                    }
                }
                $first_link = false; //set first link to false
            }

            if ($first_link) {
                $pagination .= '<li class="first active">' . $current_page . '</li>';
            } elseif ($current_page == $total_pages) {
                $pagination .= '<li class="last active">' . $current_page . '</li>';
            } else {
                $pagination .= '<li class="active">' . $current_page . '</li>';
            }

            for ($i = $current_page + 1; $i < $right_links; $i++) {
                if ($i <= $total_pages) {
                    $pagination .= '<li><a href="javascript:void(0)" data-page="' . $i . '"
					onclick="' . str_replace('{page_number}', $i, $ajax_call_function) . '"
					title="Page ' . $i . '">' . $i . '</a></li>';
                }
            }
            if ($current_page < $total_pages) {
                $next_link = ($i > $total_pages) ? $total_pages : $i;
                $pagination .= '<li><a href="javascript:void(0)" data-page="' . $next_link . '"
					onclick="' . str_replace('{page_number}', $next_link, $ajax_call_function) . '"
					title="Next">&gt;</a></li>'; //next link
                $pagination .= '<li class="last"><a href="javascript:void(0)" data-page="' . $total_pages . '"
					onclick="' . str_replace('{page_number}', $total_pages, $ajax_call_function) . '"
					title="Last">&raquo;</a></li>'; //last link
            }

            $pagination .= '</div></ul>';
            return array(
                'paging' => $summary_txt . $pagination,
                'serial' => $record_start);
        }
        return array(
            'paging' => '',
            'serial' => 1);
    }

    public function getGuestOrder($reference_id, $email)
    {
        $orders = array();
        $orders[0] = $this->getOrder(false, 0, $reference_id, $email);

        if (isset($orders[0]['rm_customer_id']) && $orders[0]['rm_customer_id'] > 0) {
            $id_customer = $orders[0]['rm_customer_id'];
            $order_found = true;
        } else {
            $id_customer = 0;
            $order_found = false;
        }
        $select_customer_info = 'select firstname, lastname,email from ' . _DB_PREFIX_ . 'customer
			where id_customer=' . (int) $id_customer . '
			and id_lang=' . (int) $this->context->language->id . ' and id_shop=' . (int) $this->context->shop->id;
        $customer_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($select_customer_info);

        $return_history = $this->getReturnHistory($id_customer);

        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            $ps_base_url = _PS_BASE_URL_SSL_;
        } else {
            $ps_base_url = _PS_BASE_URL_;
        }

        $this->context->smarty->assign(array(
            'isLogged' => ($this->context->customer->isLogged()) ? true : false,
            'orders' => $orders,
            'customer_info' => $customer_info,
            'return_history' => $return_history,
            'path' => $ps_base_url . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_),
            'module_link' => $this->context->link->getModuleLink('returnmanager', 'manager')
        ));

        $settings = Tools::unSerialize(Configuration::get('VELSOF_RETURNMANAGER'));
        if (isset($settings['enable_return_slip']) && $settings['enable_return_slip'] == 1) {
            $this->context->smarty->assign('return_slip', 1);
        } else {
            $this->context->smarty->assign('return_slip', 0);
        }
        unset($settings);

        $arr = array(
            'order_found' => $order_found,
            'template' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'returnmanager/views/templates/front/order_detail.tpl'
            )
        );
        return $arr;
    }

    public function getOrderAdmin($reference_id, $email)
    {
        $orders = array();
        $orders[0] = $this->getOrder(false, 0, $reference_id, $email);

        if (isset($orders[0]['rm_customer_id']) && $orders[0]['rm_customer_id'] > 0) {
            $id_customer = $orders[0]['rm_customer_id'];
            $order_found = true;
        } else {
            $id_customer = 0;
            $order_found = false;
        }
        $select_customer_info = 'select firstname, lastname,email from ' . _DB_PREFIX_ . 'customer
			where id_customer=' . (int) $id_customer . '
			and id_lang=' . (int) $this->context->language->id . ' and id_shop=' . (int) $this->context->shop->id;
        $customer_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($select_customer_info);

        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            $ps_base_url = _PS_BASE_URL_SSL_;
        } else {
            $ps_base_url = _PS_BASE_URL_;
        }
        $this->context->smarty->assign(array(
            'orders' => $orders,
            'customer_info' => $customer_info,
            'path' => $ps_base_url . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_),
            'module_link' => $this->context->link->getModuleLink('returnmanager', 'manager')
        ));
        $arr = array(
            'order_found' => $order_found,
            'template' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'returnmanager/views/templates/admin/order_detail_admin.tpl'
            )
        );
        return $arr;
    }

    public function getOrder($is_logged, $id_order, $reference_id = null, $email = null)
    {
        if (!$is_logged) {
            $qry = 'select ord.id_order,cust.id_customer from ' .
                _DB_PREFIX_ . 'orders ord, ' . _DB_PREFIX_ . 'customer cust
                where ord.reference= "' . pSQL($reference_id) . '"
                and cust.email= "' . pSQL($email) . '"
                and ord.id_customer=cust.id_customer
                and ord.id_shop=' . (int) $this->context->shop->id;
        } else {
            $qry = 'select ord.id_order,cust.id_customer from ' . _DB_PREFIX_ .
                'orders ord, ' . _DB_PREFIX_ . 'customer cust
                where ord.id_order= "' . (int) $id_order . '"
                and ord.id_customer=cust.id_customer
                and ord.id_shop=' . (int) $this->context->shop->id;
        }

        $order_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);

        $order_detail = array();
        if ($order_id && $order_id['id_order'] != '' && $order_id['id_order'] > 0) {
            $order = new Order((int) $order_id['id_order']);
            $reference_id = $order->reference;
            $order_state = new OrderState($order->current_state, $this->context->language->id);
            $order_status = $order_state->name;
            $order_status_color = $order_state->color;
            $order_date = Tools::displayDate($order->date_add, $this->context->language->id, true);
            $total = Tools::displayprice($order->total_products_wt);
            $total_paid = Tools::displayprice($order->total_paid_tax_incl);

            $product_detail = $order->getCartProducts();
            $products = array();
            $custom_ssl_var = 0;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }

            if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
                $ps_base_url = _PS_BASE_URL_SSL_;
            } else {
                $ps_base_url = _PS_BASE_URL_;
            }
            foreach ($product_detail as $pro) {
                $product = array();
                $product['id_order_detail'] = $pro['id_order_detail'];
                $product['id_product'] = $pro['product_id'];
                if (strpos($pro['product_name'], ' - ')) {
                    $temp = explode(' - ', $pro['product_name']);
                    $product['name'] = trim($temp[0]);
                    $product['attributes'] = explode(',', trim($temp[1]));
                } else {
                    $product['name'] = $pro['product_name'];
                    $product['attributes'] = array();
                }

                if (isset($pro['image']->id_image)) {
                    $product['id_image'] = $pro['image']->id_image;
                } else {
                    $product['id_image'] = 0;
                }

                $product['quantity'] = $pro['product_quantity'];
                $product['price'] = Tools::displayprice($pro['total_price_tax_incl']);

                $is_delivered = false;
                $is_returnable = false;
                $is_creditable = false;
                $credit_days = 0;
                $is_refundable = false;
                $refund_days = 0;
                $replacement_days = 0;
                $is_replacement = false;

                //Check for Deliver
                if ($order->hasBeenDelivered()) {
                    $is_delivered = true;
                }

                $already_returned = false;

                if ($is_delivered) {
                    $product['rm_policy_id'] = $this->getDefaultPolicy($pro['id_product'], $pro['id_category_default']);
                    $sql = 'SELECT SUM(quantity) as total from ' . _DB_PREFIX_ . 'velsof_rm_order
                        where id_order_detail = ' . (int) $pro['id_order_detail']
                        . ' AND id_shop = ' . (int) $this->context->shop->id;
                    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                    $product['total_return_qty'] = $result['total'];
                    if ($product['total_return_qty'] >= $product['quantity']) {
                        $already_returned = true;
                    } else {
                        //check for returnable
                        $qry = 'select rd.credit_days, rd.refund_days, rd.replacement_days from ' .
                            _DB_PREFIX_ . 'velsof_return_data as rd
							WHERE rd.policy = 1 AND rd.active = 1 AND rd.return_data_id = ' .
                            (int) $product['rm_policy_id'];
                        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
                        if ($result && is_array($result)) {
                            $is_returnable = true;
                            $current_date = date('Y-m-d H:i:s');
                            $delivery_date = $order->delivery_date;

                            //is_creditable
                            $calculated_date = date(
                                'Y-m-d H:i:s',
                                strtotime('+' . $result['credit_days'] . ' day', strtotime($delivery_date))
                            );
                            if ($current_date < $calculated_date) {
                                $time_diff2 = strtotime($calculated_date) - strtotime($current_date);
                                $credit_days = ceil(($time_diff2) / (60 * 60 * 24));
                                if ($credit_days > 0) {
                                    $is_creditable = true;
                                }
                            }

                            //Is Refundable
                            $calculated_date = date(
                                'Y-m-d H:i:s',
                                strtotime('+' . $result['refund_days'] . ' day', strtotime($delivery_date))
                            );
                            if ($current_date < $calculated_date) {
                                $time_diff1 = strtotime($calculated_date) - strtotime($current_date);
                                $refund_days = ceil(($time_diff1) / (60 * 60 * 24));
                                if ($refund_days > 0) {
                                    $is_refundable = true;
                                }
                            }

                            //Has replacement
                            $calculated_date = date(
                                'Y-m-d H:i:s',
                                strtotime('+' . $result['replacement_days'] . ' day', strtotime($delivery_date))
                            );
                            if ($current_date < $calculated_date) {
                                $time_diff = strtotime($calculated_date) - strtotime($current_date);
                                $replacement_days = ceil(($time_diff) / (60 * 60 * 24));
                                if ($replacement_days > 0) {
                                    $is_replacement = true;
                                }
                            }
                        }
                    }
                }
                $product['id_order'] = $order_id['id_order'];
                $product['is_delivered'] = $is_delivered;
                $product['is_returnable'] = $is_returnable;
                $product['is_creditable'] = $is_creditable;
                $product['already_returned'] = $already_returned;
                if (isset($pro['image'])) {
                    $image = new Image($pro['image']->id_image);
                    $product['pro_img'] = $ps_base_url . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
                } else {
                    $product['pro_img'] = $ps_base_url . __PS_BASE_URI__ .
                        '/modules/returnmanager/views/img/No-image.jpg';
                }
                $product['credit_days'] = $credit_days;
                $product['is_refundable'] = $is_refundable;
                $product['refund_days'] = $refund_days;
                $product['is_replacement'] = $is_replacement;
                $product['replacement_days'] = $replacement_days;
                $product['product_link'] = $this->context->link->getProductLink($pro['product_id']);
                $products[] = $product;
                unset($image);
            }
            $order_detail = array(
                'products' => $products,
                'order_id' => $id_order,
                'reference_id' => $reference_id,
                'order_state' => $order_status,
                'order_state_color' => $order_status_color,
                'cart_total' => $total,
                'total_paid' => $total_paid,
                'rm_customer_id' => $order_id['id_customer'],
                'order_date' => $order_date,
            );
        }
        return $order_detail;
    }

    public function getReturnHistory($id_customer)
    {
        $get_returns = 'select * from ' . _DB_PREFIX_ .
            'velsof_rm_order od where id_customer=' . (int) $id_customer . ' and
            od.id_shop=' . (int) $this->context->shop->id .
            ' order by date_update desc';
        $return_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($get_returns);
        $return_history = array();
        $flag = 0;
        foreach ($return_data as $return) {
            $get_status = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_status where id_rm_order=' .
                (int) $return['id_rm_order'] . ' order by date_add desc';
            $return_status = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_status);

            $get_stat_name = 'select value from ' . _DB_PREFIX_ . 'velsof_return_data_lang where id_shop=' . (int) $this->context->shop->id . ' and return_data_id=' .
                (int) $return_status['id_rm_status'];
            $status_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_stat_name);
            $return_history[$flag]['status'] = $status_name['value'];

            $get_name = 'select product_name,product_attribute_id,product_id from ' . _DB_PREFIX_ . 'order_detail
				where id_order_detail=' . (int) $return['id_order_detail'] .
                ' and id_shop=' . (int) $this->context->shop->id;
            $pro_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_name);
            if ($pro_name['product_attribute_id'] != 0) {
                $name_attr = explode(' - ', $pro_name['product_name']);
                $return_history[$flag]['product_name'] = $name_attr[0];
                $return_history[$flag]['product_attr'] = $name_attr[1];
            } else {
                $return_history[$flag]['product_name'] = $pro_name['product_name'];
                $return_history[$flag]['product_attr'] = '';
            }
            $parameters = array(
                'return_id' => (int) $return['id_rm_order']);
            $return_history[$flag]['product_id'] = $pro_name['product_id'];
            $return_history[$flag]['return_type'] = $this->l(Tools::ucfirst($return['return_type']), 'common');
            $return_history[$flag]['comment'] = $return['comment'];
            $return_history[$flag]['quantity'] = $return['quantity'];
            $return_history[$flag]['product_link'] = $this->context->link->getProductLink($pro_name['product_id']);
            $return_history[$flag]['request_date'] = Tools::displayDate($return['date_add'], $this->context->language->id) ;
            $return_history[$flag]['active'] = $return['active'];
            $return_history[$flag]['slip_link'] = $this->context->link->getModuleLink(
                'returnmanager',
                'slip',
                $parameters
            );
            $flag++;
        }
        return $return_history;
    }

    /*
     * Function to get Product Information
     * This information will be displayed in return form
     */

    public function getRequestForm()
    {
        $id_infos = explode('_', Tools::getValue('id_info'));
        $product_detail_found = false;

        $ret_typ = Tools::unSerialize(Configuration::get('VELSOF_RETURNMANAGER'));
        $product_obj = new Product($id_infos[2]);
        $get_default_category = $product_obj->getDefaultCategory();
        $policy_id = $this->getDefaultPolicy($id_infos[2], $get_default_category);

        //Get Return policy for this product
        $qry = 'Select rd.return_data_id, rd.credit_days, rd.refund_days, rd.replacement_days,
			rdl.value as policy_title, rdl.terms, rdl.credit_message, rdl.refund_message, rdl.replacement_message
			from ' . _DB_PREFIX_ . 'velsof_return_data as rd INNER JOIN ' .
            _DB_PREFIX_ . 'velsof_return_data_lang as rdl
			on (rd.return_data_id = rdl.return_data_id)
			WHERE rd. active = 1 AND rd.policy = 1 AND rdl.id_shop = ' . (int) $this->context->shop->id
            . '  AND rd.return_data_id = ' .
            (int) $policy_id;

        $policy = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);

        if ($policy && is_array($policy)) {
            $product_detail_found = true;
            $order = new Order($id_infos[0]);
            $order_product_detail = new OrderDetail($id_infos[1]);

            $product = array();

            $product['id_order'] = $order_product_detail->id_order;
            $product['odr_reference'] = $order->reference;
            $product['id_order_detail'] = $id_infos[1];
            $product['id_product'] = $order_product_detail->product_id;
            $product['id_product_attribute'] = $order_product_detail->product_attribute_id;
            $product['product_quantity'] = $order_product_detail->product_quantity;
            $product['shipping_address'] = $this->getReturnSlipDataByLanguage(
                'address',
                $this->context->language->iso_code
            );

            $p_temp = new Product($order_product_detail->product_id);
            $image_combination = $p_temp->getCombinationImages($this->context->language->id);
            if (isset($image_combination[$order_product_detail->product_attribute_id][0]['id_image'])) {
                $product['id_image'] = $order_product_detail->product_id . '-' .
                    $image_combination[$order_product_detail->product_attribute_id][0]['id_image'];
            } else {
                $get_cover_image = Product::getCover($order_product_detail->product_id);
                $product['id_image'] = $order_product_detail->product_id . '-' .
                    $get_cover_image['id_image'];
            }
            $product['link_rewrite'] = $p_temp->link_rewrite[$this->context->language->id];

            if (Context::getContext()->controller->controller_type == 'admin') {
                $link_obj = new Link();
                if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                    $product['img_path'] = 'https://' .
                        $link_obj->getImageLink($product['link_rewrite'], $product['id_image']);
                } else {
                    $product['img_path'] = 'http://' .
                        $link_obj->getImageLink($product['link_rewrite'], $product['id_image']);
                }
            }

            if (strpos($order_product_detail->product_name, ' - ')) {
                $temp = explode(' - ', $order_product_detail->product_name);
                $product['name'] = trim($temp[0]);
                $product['attributes'] = explode(',', trim($temp[1]));
            } else {
                $product['name'] = $order_product_detail->product_name;
                $product['attributes'] = array();
            }

            $sql = 'SELECT SUM(quantity) as total from ' . _DB_PREFIX_ . 'velsof_rm_order
				where id_order_detail = ' . (int) $product['id_order_detail']
                . ' AND id_shop = ' . (int) $this->context->shop->id;
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            $product['product_quantity'] = $product['product_quantity'] - $result['total'];

            $current_date = date('Y-m-d H:i:s');
            $delivery_date = $order->delivery_date;

            //Code added by Harsh to list only options which are applicable as return type on 14-Apr-2017
            //is_creditable
            $calculated_date = date(
                'Y-m-d H:i:s',
                strtotime('+' . $policy['credit_days'] . ' day', strtotime($delivery_date))
            );
            if ($current_date < $calculated_date) {
                $time_diff2 = strtotime($calculated_date) - strtotime($current_date);
                $credit_days = ceil(($time_diff2) / (60 * 60 * 24));
                if ($credit_days > 0) {
                    $is_creditable = true;
                }
            }

            //Is Refundable
            $calculated_date = date(
                'Y-m-d H:i:s',
                strtotime('+' . $policy['refund_days'] . ' day', strtotime($delivery_date))
            );
            if ($current_date < $calculated_date) {
                $time_diff1 = strtotime($calculated_date) - strtotime($current_date);
                $refund_days = ceil(($time_diff1) / (60 * 60 * 24));
                if ($refund_days > 0) {
                    $is_refundable = true;
                }
            }

            //Has replacement
            $calculated_date = date(
                'Y-m-d H:i:s',
                strtotime('+' . $policy['replacement_days'] . ' day', strtotime($delivery_date))
            );
            if ($current_date < $calculated_date) {
                $time_diff = strtotime($calculated_date) - strtotime($current_date);
                $replacement_days = ceil(($time_diff) / (60 * 60 * 24));
                if ($replacement_days > 0) {
                    $is_replacement = true;
                }
            }

            $return_types = array();
            if ($policy['credit_days'] > 0 && isset($ret_typ['credit']) && $ret_typ['credit'] == 1 && isset($is_creditable) && $is_creditable) {
                $return_types[] = array(
                    'text' => $this->l('Credit', 'common'),
                    'value' => 'credit',
                    'note' => $policy['credit_message']
                );
            }
            if ($policy['refund_days'] > 0 && isset($ret_typ['refund']) && $ret_typ['refund'] == 1 && isset($is_refundable) && $is_refundable) {
                $return_types[] = array(
                    'text' => $this->l('Refund', 'common'),
                    'value' => 'refund',
                    'note' => $policy['refund_message']
                );
            }
            if ($policy['replacement_days'] > 0 && isset($ret_typ['replacement']) && $ret_typ['replacement'] == 1 && isset($is_replacement) && $is_replacement) {
                $return_types[] = array(
                    'text' => $this->l('Replacement', 'common'),
                    'value' => 'replacement',
                    'note' => $policy['replacement_message']
                );
            }
            $rm_toc = $policy['terms'];

            //Get Return policy for this product
            $qry = 'Select rd.return_data_id, rd.whopayshipping, rdl.value
				from ' . _DB_PREFIX_ . 'velsof_return_data as rd
				INNER JOIN ' . _DB_PREFIX_ . 'velsof_return_data_lang as rdl on (rd.return_data_id = rdl.return_data_id)
				WHERE rd. active = 1 AND rd.reason = 1 AND rdl.id_lang = ' . (int) $this->context->language->id . ' and rdl.id_shop = ' . (int) $this->context->shop->id;
            $reasons_rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($qry);
            $reasons = array();
            $shipp_adm = $this->l('Shipping Charge Paid By Store Owner', 'common');
            $shipp_cust = $this->l('Shipping Charge Paid By Customer', 'common');
            if ($reasons_rs && count($reasons_rs) > 0) {
                foreach ($reasons_rs as $reason) {
                    $reasons[] = array(
                        'reason_id' => $reason['return_data_id'],
                        'text' => $reason['value'],
                        'shipping_paid_by' => ($reason['whopayshipping'] == 'so') ? $shipp_adm : $shipp_cust
                    );
                }
            }

            $product['return_types'] = $return_types;
            $product['return_toc'] = $rm_toc;
            $product['reasons'] = $reasons;
            $product['customer_id'] = $id_infos[3];
            $product['policy_id'] = $id_infos[4];
            $this->context->smarty->clearCache(
                _PS_MODULE_DIR_ . 'returnmanager/views/templates/front/rm_request_form.tpl'
            );

            $custom_ssl_var = 0;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }

            if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
                $ps_base_url = _PS_BASE_URL_SSL_;
            } else {
                $ps_base_url = _PS_BASE_URL_;
            }
            $ret_typ['enable_image_upload'] = isset($ret_typ['enable_image_upload']) ? $ret_typ['enable_image_upload'] : 0;
            $enable_image_upload = $ret_typ['enable_image_upload'];
            $this->context->smarty->assign(array(
                'enable_image_upload' => $enable_image_upload,
                'product' => $product,
                'path' => $ps_base_url . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_),
                'module_link' => $this->context->link->getModuleLink('returnmanager', 'manager')
            ));
            $template = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'returnmanager/views/templates/front/rm_request_form.tpl'
            );
        } else {
            $template = '';
        }

        $arr = array(
            'detail_found' => $product_detail_found,
            'template' => $template
        );

        return $arr;
    }

    public function getFormattedAddress($address)
    {
        $addr_html = '';
        foreach ($address['ordered_fields'] as $field) {
            if (!strpos(trim($field), ' ') && !strpos(trim($field), ',')) {
                $addr_html .= $address['ordered_fields_values'][trim($field)];
            } else {
                if (strpos($field, ',')) {
                    $temp = explode(',', trim($field));
                    foreach ($temp as $a1) {
                        if (!strpos(trim($a1), ' ')) {
                            $addr_html .= $address['ordered_fields_values'][trim($a1)];
                        } else {
                            $temp1 = explode(' ', trim($a1));
                            foreach ($temp1 as $x1) {
                                $addr_html .= $address['ordered_fields_values'][trim($x1)] . ' ';
                            }
                        }
                        $addr_html .= ',';
                    }
                } elseif (strpos(trim($field), ' ')) {
                    $temp = explode(' ', trim($field));
                    foreach ($temp as $a1) {
                        $addr_html .= $address['ordered_fields_values'][trim($a1)] . ' ';
                    }
                }
            }
            $addr_html .= '<br>';
        }
        return $addr_html;
    }

    public function submitReturnRequest()
    {
        $order = new Order(Tools::getValue('id_order'));
        $get_days = 'select ' . Tools::getValue('rm_return_type') . '_days from ' . _DB_PREFIX_ .
            'velsof_return_data where
			return_data_id=' . (int) Tools::getValue('id_policy');
        $days_applicable = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_days);
        $get_shipping = 'select whopayshipping from ' . _DB_PREFIX_ . 'velsof_return_data where
			return_data_id=' . (int) Tools::getValue('rm_return_reason');
        $pay_shp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_shipping);
        if ($pay_shp['whopayshipping'] == 'c') {
            $shp = 1;
        } elseif ($pay_shp['whopayshipping'] == 'so') {
            $shp = 2;
        }
        $data_to_update = array(
            'id_customer' => (int) Tools::getValue('id_customer'),
            'id_order' => (int) Tools::getValue('id_order'),
            'comment' => '<pre>' . Tools::getValue('rm_comment') . '<pre>',
            'id_order_detail' => (int) Tools::getValue('id_order_detail'),
            'quantity' => (int) Tools::getValue('rm_return_quantity')
        );
        $id_order_return = $this->updateRMATables('return_created', $data_to_update);
        if (empty($id_order_return)) {
            $id_order_return = 0;
        }
        $plugin_data = Tools::unSerialize(Configuration::get('VELSOF_RETURNMANAGER'));

        $image_value = '';
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            if (!empty($file['name'])) {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
//                $allowed_extension = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', $kbcustomfield->file_extension);
//                if (in_array($file_extension, $allowed_extension)) {
                $image_name = $id_order_return . '_velsof_return_' . time() . '.' . $file_extension;
                $path = _PS_IMG_DIR_ . 'velsof_return/' . $image_name;
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $path
                );
                chmod(_PS_IMG_DIR_ . 'velsof_return/' . $image_name, 0777);
                $image_value = $image_name;
//                    $image_value = Tools::jsonEncode(
//                        array(
//                            'path' => $path,
//                            'type' => $file['type'],
//                            'extension' => $file_extension
//                        )
//                    );
//                } else {
//                    $image_value = '';
//                }
            } else {
                $image_value = '';
            }
        }

        $return_request_qry = 'insert into ' . _DB_PREFIX_ .
            'velsof_rm_order (`id_rm_order`, `id_order_return`, `id_customer`,
			`id_order`, `id_shop`, `id_lang`,
			`id_rm_policy`, `return_type`, `days_applicable`, `id_rm_reason`,`whopayshipping`, `comment`, `image_path`,
            `id_order_detail`, `quantity`, `date_add`,
			`date_update`) values ("", ' . (int) $id_order_return . ', ' . (int) Tools::getValue('id_customer') . ', '
            . (int) Tools::getValue('id_order') . ', ' . (int) $this->context->shop->id .
            ', ' . (int) $this->context->language->id . ','
            . (int) Tools::getValue('id_policy') . ', "' . pSQL(Tools::getValue('rm_return_type')) . '",
			' . (int) $days_applicable[Tools::getValue('rm_return_type') . '_days'] .
            ',' . (int) Tools::getValue('rm_return_reason') . ',' . pSQL($shp) . ',
            "' . pSQL('<pre>' . Tools::getValue('rm_comment') . '</pre>') . '","' . pSQL($image_value) . '" ,' .
            (int) Tools::getValue('id_order_detail') . ',' .
            (int) Tools::getValue('rm_return_quantity') . ', now(), now())';
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($return_request_qry);
        $return_id = Db::getInstance()->Insert_ID();
        $return_status_qry = 'insert into ' . _DB_PREFIX_ . 'velsof_rm_status (`id_rm_order`, `id_rm_status`,
			`date_add`) values (' . (int) $return_id . ', ' . (int) $plugin_data['status']['default'] . ', now())';
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($return_status_qry);

        $temp_address = new Address($order->id_address_delivery);
        $address = array();
        $address['ordered_fields'] = AddressFormat::getOrderedAddressFields($temp_address->id_country);
        $address['ordered_fields_values'] = AddressFormat::getFormattedAddressFieldsValues(
            $temp_address,
            $address['ordered_fields']
        );
        $shipping_address = $this->getFormattedAddress($address);
        if (Configuration::get('PS_TAX')) {
            $sub_total = $order->total_products_wt;
            if ($order->total_shipping > 0) {
                $shipping_charge = $order->total_shipping_tax_incl;
            } else {
                $shipping_charge = 0;
            }
            $order_total = $order->total_paid_tax_incl;
        } else {
            $sub_total = $order->total_products;
            if ($order->total_shipping > 0) {
                $shipping_charge = $order->total_shipping_tax_excl;
            } else {
                $shipping_charge = 0;
            }
            $order_total = $order->total_paid_tax_excl;
        }

        $ordered_products = $order->getCartProducts();
        $products = array();
        $returned_product = array();
        foreach ($ordered_products as $pro) {
            $product = array();
            $p_temp = new Product($pro['product_id']);
            $image_combination = $p_temp->getCombinationImages($this->context->language->id);
            if (isset($image_combination[$pro['product_attribute_id']][0]['id_image'])) {
                $product['id_image'] = $pro['product_id'] . '-' .
                    $image_combination[$pro['product_attribute_id']][0]['id_image'];
            } else {
                $get_cover_image = Product::getCover($pro['product_id']);
                $product['id_image'] = $pro['product_id'] . '-' . $get_cover_image['id_image'];
            }
            $product['link_rewrite'] = $p_temp->link_rewrite[$this->context->language->id];

            if (Context::getContext()->controller->controller_type == 'admin') {
                $link_obj = new Link();
                if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                    $product['img_path'] = 'https://' . $link_obj->getImageLink(
                        $product['link_rewrite'],
                        $product['id_image']
                    );
                } else {
                    $product['img_path'] = 'http://' . $link_obj->getImageLink(
                        $product['link_rewrite'],
                        $product['id_image']
                    );
                }
            }

            if (strpos($pro['product_name'], ' - ')) {
                $temp = explode(' - ', $pro['product_name']);
                $product['name'] = trim($temp[0]);
                $product['attributes'] = explode(',', trim($temp[1]));
            } else {
                $product['name'] = $pro['product_name'];
                $product['attributes'] = array();
            }
            $product['quantity'] = $pro['product_quantity'] - $pro['product_quantity_return'];

            if (Configuration::get('PS_TAX')) {
                $product['unit_price'] = $pro['unit_price_tax_incl'];
            } else {
                $product['unit_price'] = $pro['unit_price_tax_excl'];
            }

            $products[] = $product;

            if ($pro['id_order_detail'] == Tools::getValue('id_order_detail')) {
                $product['quantity'] = Tools::getValue('rm_return_quantity');
                $returned_product = $product;
            }
        }

        $success_string = '';
        $success_msg = '';
        if (Tools::getValue('rm_return_type') == 'credit') {
            $success_string = sprintf(
                '%s ' . $this->l('request successfully created', 'common'),
                $this->l('Credit', 'common')
            );
            $success_msg = $this->getMessageByName('credit', $this->context->language->iso_code);
        }
        if (Tools::getValue('rm_return_type') == 'refund') {
            $success_string = sprintf(
                '%s ' . $this->l('request successfully created', 'common'),
                $this->l('Refund', 'common')
            );
            $success_msg = $this->getMessageByName('refund', $this->context->language->iso_code);
        }
        if (Tools::getValue('rm_return_type') == 'replacement') {
            $success_string = sprintf(
                '%s ' . $this->l('request successfully created', 'common'),
                $this->l('Replacement', 'common')
            );
            $success_msg = $this->getMessageByName('replace', $this->context->language->iso_code);
        }

        $get_reason = 'select l.value from ' . _DB_PREFIX_ . 'velsof_return_data_lang l,' .
            _DB_PREFIX_ . 'velsof_return_data ret where
			ret.return_data_id=l.return_data_id and l.id_shop=' . (int) $this->context->shop->id .
            ' and ret.return_data_id=' . (int) Tools::getValue('rm_return_reason');

        $return_reason = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_reason);
        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            $ps_base_url = _PS_BASE_URL_SSL_;
        } else {
            $ps_base_url = _PS_BASE_URL_;
        }
        $this->context->smarty->assign(
            array(
                'products' => $products,
                'returned_product' => $returned_product,
                'shipping_address' => $shipping_address,
                'sub_total' => $sub_total,
                'shipping_charge' => $shipping_charge,
                'order_total' => $order_total,
                'currency' => new Currency($order->id_currency),
                'return_id' => $return_id,
                'customer_commet' => '<pre>' . trim(Tools::getValue('rm_comment')) . '</pre>',
                'success_message' => $success_string,
                'success_msg' => $success_msg,
                'return_reason' => $return_reason['value'],
                'path' => $ps_base_url . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_),
                'module_link' => $this->context->link->getModuleLink('returnmanager', 'manager')
            )
        );

        $this->context->smarty->clearCache(
            _PS_MODULE_DIR_ . 'returnmanager/views/templates/front/rm_return_submit_success.tpl'
        );

        $return_data = array(
            'return_id' => $return_id,
            'order_reference' => $order->reference,
            'id_order' => (int) Tools::getValue('id_order'),
            'id_customer' => (int) Tools::getValue('id_customer')
        );
        $this->sendNotificationEmail('new_ret_cust', $return_data);
        $this->sendNotificationEmail('new_ret_adm', $return_data);
        $arr = array(
            'template' => $this->context->smarty->fetch(_PS_MODULE_DIR_ .'returnmanager/views/templates/front/rm_return_submit_success.tpl'),
            'return_id' => $return_id
        );
        return $arr;
    }

    protected function getReturnData($return_id)
    {
        $status_history = array();
        $flag = 0;
        $status_qry = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_status where id_rm_order=' .
            (int) $return_id . ' order by date_add desc';
        $status_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($status_qry);
        foreach ($status_data as $status) {
            $get_stat_name = 'select value from ' . _DB_PREFIX_ . 'velsof_return_data_lang where id_shop=' . (int) $this->context->shop->id . ' and return_data_id=' .
                (int) $status['id_rm_status'];
            $status_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_stat_name);
            $status_history[$flag]['status'] = $status_name['value'];
//            $status_history[$flag]['date'] = date('d-M-Y (h:i A)', strtotime($status['date_add']));
            $status_history[$flag]['date'] = Tools::displayDate($status['date_add'], $this->context->language->id, true);
            $flag++;
        }

        $get_return = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_order od where id_rm_order=' .
            (int) $return_id . ' and
            od.id_shop=' . (int) $this->context->shop->id;
        $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_return);

        $get_reason_name = 'select l.value from ' . _DB_PREFIX_ . 'velsof_return_data_lang l,' . _DB_PREFIX_ .
            'velsof_return_data d where
            l.id_shop=' . (int) $this->context->shop->id . ' and d.return_data_id=' .
            (int) $return['id_rm_reason'] . ' and
            l.return_data_id=d.return_data_id';
        $status_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_reason_name);
        $return_data = array();
        $return_data['reason'] = $status_name['value'];
        $return_data['return_id'] = $return['id_rm_order'];
        $get_name = 'select product_name,product_attribute_id,product_id,unit_price_tax_incl
            from ' . _DB_PREFIX_ . 'order_detail where id_order_detail=' . (int) $return['id_order_detail'] .
            ' and id_shop=' . (int) $this->context->shop->id;
        $pro_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_name);
        $product_image = Image::getImages(
            $this->context->language->id,
            $pro_name['product_id'],
            $pro_name['product_attribute_id']
        );
        $image = new Image($product_image[0]['id_image']);
        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            $ps_base_url = _PS_BASE_URL_SSL_;
        } else {
            $ps_base_url = _PS_BASE_URL_;
        }
        $return_data['pro_img'] = $ps_base_url . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
        if ($pro_name['product_attribute_id'] != 0) {
            $name_attr = explode(' - ', $pro_name['product_name']);
            $return_data['product_name'] = $name_attr[0];
            $return_data['product_attr'] = $name_attr[1];
        } else {
            $return_data['product_name'] = $pro_name['product_name'];
            $return_data['product_attr'] = '';
        }

        $cust_obj = new Customer($return['id_customer']);
        $odr_obj = new Order($return['id_order']);
        $return_data['cust_name'] = $cust_obj->firstname . ' ' . $cust_obj->lastname;
        $return_data['email'] = $cust_obj->email;
        $return_data['product_link'] = $this->context->link->getProductLink($pro_name['product_id']);
        $return_data['order_reference'] = $odr_obj->reference;
        $return_data['order_total'] = Tools::displayPrice($odr_obj->total_paid_tax_incl);
        $return_data['order_shipping'] = Tools::displayPrice($odr_obj->total_shipping_tax_incl);
        $return_data['id_order'] = $return['id_order'];
//        $return_data['order_date'] = date('d-M-Y', strtotime($odr_obj->date_add,$this->context->language->id));
        $return_data['order_date'] = Tools::displayDate($odr_obj->date_add, $this->context->language->id);
        $order_state = new OrderState($odr_obj->current_state, $this->context->language->id);
        $return_data['order_status'] = $order_state->name;
        $return_data['order_status_color'] = $order_state->color;
        $return_data['quantity'] = $return['quantity'];
        $return_data['unit_price_tax_incl'] = Tools::displayPrice($pro_name['unit_price_tax_incl']);
        $return_data['unit_price_unformatted'] = $pro_name['unit_price_tax_incl'];

        $temp_address = new Address($odr_obj->id_address_delivery);
        $address = array();
        $address['ordered_fields'] = AddressFormat::getOrderedAddressFields($temp_address->id_country);
        $address['ordered_fields_values'] = AddressFormat::getFormattedAddressFieldsValues(
            $temp_address,
            $address['ordered_fields']
        );
        $return_data['shipping_address'] = $this->getFormattedAddress($address);

        $return_data['whopayshipping'] = $return['whopayshipping'];
        $return_detail = array();
        $return_detail[0] = $status_history;
        $return_detail[1] = $return_data;
        unset($cust_obj);
        unset($odr_obj);
        unset($order_state);
        unset($temp_address);
        unset($image);
        return $return_detail;
    }

    protected function updateRMATables($action_type, $return_data)
    {
        switch ($action_type) {
            case 'return_created':
                $insert_rma = 'insert into ' . _DB_PREFIX_ .
                    'order_return (`id_order_return`, `id_customer`, `id_order`,
                    `question`, `date_add`, `date_upd`) values ("", ' . (int) $return_data['id_customer'] . ', '
                    . (int) $return_data['id_order'] . ', "' . pSQL($return_data['comment']) . '", now(), now())';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($insert_rma);
                $id_order_return = Db::getInstance()->Insert_ID();
                $insert_odr_det = 'insert into ' . _DB_PREFIX_ .
                    'order_return_detail (`id_order_return`, `id_order_detail`,
                    `product_quantity`) values (' . (int) $id_order_return .
                    ', ' . (int) $return_data['id_order_detail'] . ', '
                    . (int) $return_data['quantity'] . ')';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($insert_odr_det);
                break;
            case 'return_denied':
                $update_rma = 'update ' . _DB_PREFIX_ . 'order_return set state = 4 where id_order_return = ' .
                    (int) $return_data['id_order_return'];
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($update_rma);
                break;

            case 'return_completed':
                $update_rma = 'update ' . _DB_PREFIX_ . 'order_return set state = 5 where id_order_return = ' .
                    (int) $return_data['id_order_return'];
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($update_rma);
                break;
        }
        if (isset($id_order_return)) {
            return $id_order_return;
        }
    }

    protected function getDefaultMessages()
    {
        $message_arr = array(
            'credit' => 'We will send a replacement (Credit Request).
            We will pickup the item you wish to return in within 6 days.
			We hope you understand that we can only accept items for return,
            if they have not been used or tempered with.
			Original packaging and accessories also need to be returned along with the item.',
            'refund' => 'We will send a replacement (Refund Request).
            We will pickup the item you wish to return in within 6 days.
			We hope you understand that we can only accept items for return,
            if they have not been used or tempered with.
			Original packaging and accessories also need to be returned along with the item.',
            'replace' => 'We will send a replacement (Replacement Request).
            We will pickup the item you wish to return in within 6 days.
			We hope you understand that we can only accept items for return,
            if they have not been used or tempered with.
			Original packaging and accessories also need to be returned along with the item.'
        );
        return $message_arr;
    }

    protected function getMessageByName($name, $iso)
    {
        $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_success_messages where iso_code="' . pSQL($iso) .
            '" and id_shop=' . (int) $this->context->shop->id . ' and message_name = "' . pSQL($name) . '"';
        $message_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
        if ($message_data) {
            return Tools::htmlentitiesDecodeUTF8($message_data['content']);
        } else {
            $default_messages = $this->getDefaultMessages();
            return $default_messages[$name];
        }
    }

    protected function getMessagesData($id_lang)
    {
        $iso = Language::getIsoById($id_lang);
        $fetch_messages_query = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_success_messages where iso_code="' .
            pSQL($iso) .
            '" and id_shop=' . (int) $this->context->shop->id;
        $messages_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($fetch_messages_query);
        if ($messages_data) {
            $final_messages_data = array();
            foreach ($messages_data as $mess) {
                $final_messages_data[$mess['message_name']] = Tools::htmlentitiesDecodeUTF8($mess['content']);
            }
            return $final_messages_data;
        } else {
            return $this->getDefaultMessages();
        }
    }

    protected function saveMessagesData($messages, $iso)
    {
        foreach ($messages as $name => $m) {
            $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_success_messages where iso_code="' . pSQL($iso) .
                '" and id_shop=' . (int) $this->context->shop->id . ' and message_name = "' . pSQL($name) . '"';
            $message_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
            if ($message_data) {
                $qry = 'UPDATE ' . _DB_PREFIX_ . 'velsof_rm_success_messages set
					content = "' . Tools::htmlentitiesUTF8($m)
                    . '", date_upd=now() where
					id_message = ' . (int) $message_data['id_message'];
            } else {
                $qry = 'INSERT into ' . _DB_PREFIX_ . 'velsof_rm_success_messages values ("", ' .
                    (int) Language::getIdByIso($iso) . ',
				' . (int) $this->context->shop->id . ', "' . pSQL($iso) . '",
				"' . pSQL($name) . '", "' . Tools::htmlentitiesUTF8($m) . '",
				now(), now())';
            }
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($qry);
        }
    }

    protected function saveReturnSlipData($slip_data, $iso)
    {
        $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_return_slip_data where iso_code="' . pSQL($iso) .
            '" and id_shop=' . (int) $this->context->shop->id . ' and address = "1"';
        $rslip_address = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
        if ($rslip_address) {
            $qry = 'UPDATE ' . _DB_PREFIX_ . 'velsof_return_slip_data set
				html_content = "' . Tools::htmlentitiesUTF8($slip_data['return_address'])
                . '", date_upd=now() where
				id_slip_data = ' . (int) $rslip_address['id_slip_data'];
        } else {
            $qry = 'INSERT into ' . _DB_PREFIX_ . 'velsof_return_slip_data values ("", ' .
                (int) Language::getIdByIso($iso) . ',
			' . (int) $this->context->shop->id . ', "' . pSQL($iso) . '",
			"1", "0", "' . Tools::htmlentitiesUTF8($slip_data['return_address']) . '",
			now(), now())';
        }
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($qry);

        $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_return_slip_data where iso_code="' . pSQL($iso) .
            '" and id_shop=' . (int) $this->context->shop->id . ' and guideline = "1"';
        $rslip_guideline = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
        if ($rslip_guideline) {
            $qry = 'UPDATE ' . _DB_PREFIX_ . 'velsof_return_slip_data set
				html_content = "' . Tools::htmlentitiesUTF8($slip_data['return_guidelines'])
                . '", date_upd=now() where
				id_slip_data = ' . (int) $rslip_guideline['id_slip_data'];
        } else {
            $qry = 'INSERT into ' . _DB_PREFIX_ . 'velsof_return_slip_data values ("", ' .
                (int) Language::getIdByIso($iso) . ',
			' . (int) $this->context->shop->id . ', "' . pSQL($iso) . '",
			"0", "1", "' . Tools::htmlentitiesUTF8($slip_data['return_guidelines']) . '",
			now(), now())';
        }
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($qry);
    }

    protected function getReturnSlipDataByLanguage($type, $iso)
    {
        if ($type == 'address') {
            $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_return_slip_data where iso_code="' . pSQL($iso) .
                '" and id_shop=' . (int) $this->context->shop->id . ' and address = "1"';
            $slip_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
            if ($slip_data) {
                return Tools::htmlentitiesDecodeUTF8($slip_data['html_content']);
            } else {
                $address = '<p>' . Configuration::get('BLOCKCONTACTINFOS_COMPANY') . '</p>';
                $address .= '<p>' . Configuration::get('BLOCKCONTACTINFOS_ADDRESS') . '</p>';
                $address .= '<p>' . $this->getModuleTranslationByLanguage('returnmanager', 'Phone', 'common', $iso) . ': ' .
                    Configuration::get('BLOCKCONTACTINFOS_PHONE') . '</p>';
                return $address;
            }
        } elseif ($type == 'guide') {
            $qry = 'select * from ' . _DB_PREFIX_ . 'velsof_return_slip_data where iso_code="' . pSQL($iso) .
                '" and id_shop=' . (int) $this->context->shop->id . ' and guideline = "1"';
            $slip_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($qry);
            if ($slip_data) {
                return Tools::htmlentitiesDecodeUTF8($slip_data['html_content']);
            } else {
                return $this->getDefaultReturnGuidelines();
            }
        }
    }

    public function generateReturnSlip($return_id, $action = 'approve')
    {
        $query = 'Select id_lang from '._DB_PREFIX_.'velsof_rm_order where id_rm_order = '.(int)$return_id;
        $language_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        $language = Language::getIsoById($language_id);
        if (!$language) {
            $language = Language::getIsoById($this->context->language->id);
        }
        
        $bar = new TCPDFBarcode($return_id, 'C39');
        $return_data = $this->getReturnData($return_id);
        $html = $this->getReturnSlipDataByLanguage('guide', $this->context->language->iso_code);
        $html .= '<br><h2><strong>' .
        $this->getModuleTranslationByLanguage('returnmanager', 'Return Mailing Label', 'common', $language) . '</strong></h2>';
        $html .= '<p>' . $this->getModuleTranslationByLanguage('returnmanager', 'Cut this label and affix to the outside of your return package.', 'common', $language) . '</p>';
        $return_address = $this->getReturnSlipDataByLanguage('address', $this->context->language->iso_code);
        $label_content = '<table style="padding: 14px;">
			<tr><td style="width: 30%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;</td><td style="font-size: 16px; line-height: 1.5;">' . $return_address . '</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td><td style="width: 340px; text-align: center; font-weight: bold;">'
            . $this->getModuleTranslationByLanguage('returnmanager', 'FROM', 'common', $language) . '</td></tr>
			<tr><td>&nbsp;</td><td style="width: 550px; text-align: right;">_______________________</td></tr>
			<tr><td>&nbsp;</td><td style="width: 550px; text-align: right;">_______________________</td></tr>
			<tr><td>&nbsp;</td><td style="width: 550px; text-align: right;">_______________________</td></tr>
			
            </table><table style="margin: 0 auto;"><tr><td align="center">' . $bar->getBarcodeHTML() . '</td></tr>
			<tr><td align="center">' . $this->getModuleTranslationByLanguage('returnmanager', 'Return Id', 'common', $language) . ': ' .
            $return_id . '</td></tr>
            <tr><td align="center"> <strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Order', 'common', $language) . ':</strong> ' . $return_data[1]['order_reference'] . '</td></tr></table>';
        $html .= '<div style="border: 2px dashed grey; page-break-after: always;">' . $label_content . '</div>';
        $html .= $this->getModuleTranslationByLanguage('returnmanager', 'If you do not want to use the above mailing label, you can send your return package using a carrier of your choice to the following address. You will need to pay for return postage costs.', 'common', $language);
            $html .= '<br><div style="margin: 0 auto; line-height: 1;"><strong>' . $return_address . '</strong></div>';
        $html .= '<h2><strong>' .
            $this->getModuleTranslationByLanguage('returnmanager', 'Return Authorization Page', 'common', $language) .
            '</strong></h2><p>';
//        $html_comment = 'Cut this and place inside the return package with your name and signature at the bottom.';
        $html .= $this->getModuleTranslationByLanguage('returnmanager', 'Place inside the return package with your name and signature at the bottom.', 'common', $language)
            . '</p><hr style="border: 1px dashed grey;" >';
        $html .= '<br></table><table style="margin: 0 auto;"><tr><td align="center">' . $bar->getBarcodeHTML(4, 90) .
            '</td></tr>
			<tr><td align="center"><strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Return Id', 'common', $language) .
            ':</strong> ' . $return_id . '</td></tr>
			<tr><td align="center">
			<strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Order', 'common', $language) . ':</strong> ' .
            $return_data[1]['order_reference'] . '
			</td></tr></table><br>';
        $html .= '<table cellpadding="0" cellspacing="0" style="width: 100%;'
            . ' border-right:1px solid #A4A4A4; border-bottom:1px solid #A4A4A4;">';
        $html .= '<tr><td width="350" style="border-left:1px solid #A4A4A4; border-top:1px solid #A4A4A4;
			background: #E6E6E6; padding: 3px;">';
        $html .= '<strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Item Description', 'common', $language) .
            '</strong></td>';
        $html .= '<td width="80" style="border-left:1px solid #A4A4A4;'
            . ' border-top:1px solid #A4A4A4; background: #E6E6E6; padding: 3px;">';
        $html .= '<strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Total Price', 'common', $language) .
            '</strong></td>';
        $html .= '<td width="65" style="border-left:1px solid #A4A4A4; border-top:1px solid #A4A4A4;'
            . ' background: #E6E6E6; padding: 3px;">';
        $html .= '<strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Quantity', 'common', $language) .
            '</strong></td></tr><tr>';
        $html .= '<td style="border-left:1px solid #A4A4A4; border-top:1px solid #A4A4A4; padding: 3px;">';
        $html .= $return_data[1]['product_name'];
        if (isset($return_data[1]['product_attr']) && $return_data[1]['product_attr'] != '') {
            $html .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 15px; font-style: italic;">' .
                $return_data[1]['product_attr'] . '</span>';
        }
        $html .= '</td><td style="border-left:1px solid #A4A4A4; border-top:1px solid #A4A4A4; padding: 3px;">';
        $html .= Tools::displayPrice($return_data[1]['unit_price_unformatted'] * $return_data[1]['quantity']) . '</td>';
        $html .= '<td style="border-left:1px solid #A4A4A4; border-top:1px solid #A4A4A4; padding: 3px;">';
        $html .= $return_data[1]['quantity'];
        $html .= '</td></tr></table><br><p style="text-align: center;">';
        $html .= '<strong>' .
            $this->getModuleTranslationByLanguage('returnmanager', 'TO WHOM IT MAY CONCERN', 'common', $language) .
            '</strong></p><p>';
        $html .= $this->getModuleTranslationByLanguage('returnmanager', 'I hereby declare that this return package contains all the items with there accessories', 'common', $language);
        $html .= ' ' .
            $this->getModuleTranslationByLanguage('returnmanager', '(if any) related to this return request with', 'common', $language);
        $html .= ' <strong>' .
            $this->getModuleTranslationByLanguage('returnmanager', 'Return Id', 'common', $language) . ': ' .
            $return_id . '</strong> '.$this->getModuleTranslationByLanguage('returnmanager', 'against', 'common', $language);
        $html .= ' <strong>' . $this->getModuleTranslationByLanguage('returnmanager', 'Order', 'common', $language) . ': ' .
            $return_data[1]['order_reference'] . '</strong></p>';
        $html .= '<p>' .
            $this->getModuleTranslationByLanguage('returnmanager', 'I also declare that the items in this package are as it is and are not tempered with.', 'common', $language);
        $html .= ' ' .
            $this->getModuleTranslationByLanguage('returnmanager', 'You can reject the return request if anything like this is found.', 'common', $language) . '</p>';
        $html .= '<p>' .
            $this->getModuleTranslationByLanguage('returnmanager', 'Your Sincerly', 'common', $language) . ', </p><br><br>';
        $html .= '<hr style="border: 1px dashed grey;" >';

        $dompdf = new DOMPDF();
        /* Start - Code Modified by Raghu on 24-Oct-2017 for fixing the Euro Symbol Issue in Return Slip */
        if (strpos($html, '�') !== false) {
            $html = iconv('UTF-8', 'CP1252', $html);
        } else {
            $html = utf8_decode($html);
        }
        $html = str_replace('�', '&euro;', $html);
        $html = str_replace('�', '&pound;', $html);
        /* End - Code Modified by Raghu on 24-Oct-2017 for fixing the Euro Symbol Issue in Return Slip */
        $dompdf->load_html($html);
        $dompdf->render();
        if ($action == 'approve') {
            file_put_contents($this->getReturnSlipPath() . $this->getReturnSlipName($return_id), $dompdf->output());
        } elseif ($action == 'click') {
            $dompdf->stream($this->getReturnSlipName($return_id), array('Attachment' => 0));
        }
        unset($bar);
        unset($return_data);
        unset($return_address);
        return true;
    }

    protected function loadEmailTemplate($language, $template_name)
    {
        $fetch_template_query = 'select * from ' . _DB_PREFIX_ . 'velsof_rm_email where id_lang=' . (int) $language .
            ' and id_shop=' . (int) $this->context->shop->id . ' and template_name="' . pSQL($template_name) . '"';
        $template_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($fetch_template_query);
        if ($template_data) {
            $template_data['body'] = Tools::htmlentitiesDecodeUTF8($template_data['body']);
            $template_data['subject'] = Tools::htmlentitiesDecodeUTF8($template_data['subject']);
            $template_data['text_content'] = Tools::htmlentitiesDecodeUTF8($template_data['text_content']);
            return $template_data;
        } else {
            $template_data = array();
            switch ($template_name) {
                case 'new_ret_cust':
                    $template_data = $this->getDefaultNewReturnCustEmail();
                    break;
                case 'new_ret_adm':
                    $template_data = $this->getDefaultNewReturnAdmEmail();
                    break;
                case 'ret_app':
                    $template_data = $this->getDefaultReturnApprovedEmail();
                    break;
                case 'ret_den':
                    $template_data = $this->getDefaultReturnDeniedEmail();
                    break;
                case 'ret_stat':
                    $template_data = $this->getDefaultReturnStatusEmail();
                    break;
                case 'ret_comp':
                    $template_data = $this->getDefaultReturnCompletedEmail();
                    break;
            }
            $template_data['id_template'] = 0;
            return $template_data;
        }
    }

    /* Edited by Anshul Mittal On 25-08-2017 to add a functionality of email editing before sending it to customer */

    public function sendNotificationEmail($email_template, $return_data, $custom_data = array())
    {
        $customer = new Customer((int) $return_data['id_customer']);
        /* Start Addded by Anshul Mittal on 25-08-2017  to add a functionality of email editing before sending it to customer */
        if (!empty($custom_data)) {
            $template_data = array();
            $template_data['subject'] = $custom_data['subject'];
            $template_data['body'] = $custom_data['body'];
        } else {
            $template_data = $this->loadEmailTemplate($customer->id_lang, $email_template);
        }
        $query = 'Select id_lang from '._DB_PREFIX_.'velsof_rm_order where id_rm_order = '.(int)$return_data['return_id'];
        $language_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        $language = Language::getIsoById($language_id);
        if (!$language) {
            $language = Language::getIsoById($this->context->language->id);
        }
        /* End Addded by Anshul Mittal on 25-08-2017 to add a functionality of email editing before sending it to customer */
        $directory = $this->getTemplateDir();
        if (is_writable($directory)) {
            $html_template = self::TEMPLATE_NAME . '.html';
            $txt_template = self::TEMPLATE_NAME . '.txt';

            $base_html = $this->getTemplateBaseHtml();

            $template_html = str_replace('{template_content}', $template_data['body'], $base_html);
            $file = fopen($directory . $html_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            $file = fopen($directory . $txt_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            $attachment = null;
            $link_obj = new Link();
            switch ($email_template) {
                case 'new_ret_cust':
                    $template_vars = array(
                        '{customer_full_name}' => $customer->firstname . ' ' . $customer->lastname,
                        '{order_history_link}' => $link_obj->getPageLink('history'),
                        '{order_reference}' => $return_data['order_reference'],
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id']
                    );
                    break;
                case 'new_ret_adm':
                    $template_vars = array(
                        '{order_reference}' => $return_data['order_reference'],
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id']
                    );
                    break;
                case 'ret_app':
                    $odr = new Order((int) $return_data['id_order']);
                    $template_vars = array(
                        '{customer_full_name}' => $customer->firstname . ' ' . $customer->lastname,
                        '{order_history_link}' => $link_obj->getPageLink('history'),
                        '{order_reference}' => $odr->reference,
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id'],
                        '{attachment_text}' => ''
                    );
                    $settings = Tools::unSerialize(Configuration::get('VELSOF_RETURNMANAGER'));
                    if (isset($settings['enable_return_slip']) && $settings['enable_return_slip'] == 1) {
                        $file_path = $this->getReturnSlipPath() . $this->getReturnSlipName($return_data['return_id']);
                        $attachment = array(
                            'content' => Tools::file_get_contents($file_path),
                            'name' => $this->getReturnSlipName($return_data['return_id']),
                            'mime' => 'application/pdf'
                        );
                        $template_vars['{attachment_text}'] = '<p style="padding:19px 0 0 0;margin:0;
                        color:#565656;line-height:19px">
                        ' . $this->getModuleTranslationByLanguage('returnmanager', 'Please find in attachements the return slip for this return request.', 'common', $language) . '</p>';
                    }
                    unset($odr);
                    unset($settings);
                    break;
                case 'ret_den':
                    $odr = new Order((int) $return_data['id_order']);
                    $template_vars = array(
                        '{customer_full_name}' => $customer->firstname . ' ' . $customer->lastname,
                        '{order_history_link}' => $link_obj->getPageLink('history'),
                        '{order_reference}' => $odr->reference,
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id']
                    );
                    unset($odr);
                    break;
                case 'ret_stat':
                    $odr = new Order((int) $return_data['id_order']);
                    $template_vars = array(
                        '{customer_full_name}' => $customer->firstname . ' ' . $customer->lastname,
                        '{order_history_link}' => $link_obj->getPageLink('history'),
                        '{order_reference}' => $odr->reference,
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id'],
                        '{signin_link}' => $link_obj->getPageLink('authentication'),
                        '{previous_status}' => $return_data['previous_status'],
                        '{current_status}' => $return_data['current_status']
                    );
                    unset($odr);
                    break;

                case 'ret_comp':
                    $odr = new Order((int) $return_data['id_order']);
                    $template_vars = array(
                        '{customer_full_name}' => $customer->firstname . ' ' . $customer->lastname,
                        '{order_history_link}' => $link_obj->getPageLink('history'),
                        '{order_reference}' => $odr->reference,
                        '{item_details}' => $this->getItemHtml($return_data['return_id']),
                        '{return_id}' => $return_data['return_id'],
                        '{signin_link}' => $link_obj->getPageLink('authentication')
                    );
                    unset($odr);
                    break;
            }
            unset($link_obj);
            $lang_iso = Configuration::get('VELSOF_RETURN_MANAGER_DEFAULT_TEMPLATE_LANG');
            $id_lang = Language::getIdByIso($lang_iso);
            if ($email_template == 'new_ret_adm') {
                $if_data1 = Mail::Send($id_lang, self::TEMPLATE_NAME, $template_data['subject'], $template_vars, Configuration::get('PS_SHOP_EMAIL'), Configuration::get('PS_SHOP_NAME'), Configuration::get('PS_SHOP_EMAIL'), Configuration::get('PS_SHOP_NAME'), null, null, _PS_MODULE_DIR_ . 'returnmanager/mails/', false, $this->context->shop->id);
                if ($if_data1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $if_data2 = Mail::Send($id_lang, self::TEMPLATE_NAME, $template_data['subject'], $template_vars, $customer->email, $customer->firstname . ' ' . $customer->lastname, Configuration::get('PS_SHOP_EMAIL'), Configuration::get('PS_SHOP_NAME'), $attachment, null, _PS_MODULE_DIR_ . 'returnmanager/mails/', false, $this->context->shop->id);
                if ($if_data2) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    protected function getItemHtml($return_id)
    {
        $query = 'Select id_lang from '._DB_PREFIX_.'velsof_rm_order where id_rm_order = '.(int)$return_id;
        $language_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        $language = Language::getIsoById($language_id);
        if (!$language) {
            $language = Language::getIsoById($this->context->language->id);
        }
        
        
        $order_query = 'select id_order_detail,quantity from ' . _DB_PREFIX_ . 'velsof_rm_order where id_rm_order = ' .
            (int) $return_id;
        $item_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($order_query);
        $order_detail = new OrderDetail((int) $item_data['id_order_detail']);
        $price = Tools::displayPrice((float) $order_detail->unit_price_tax_incl * (int) $item_data['quantity']);

        if (strpos($order_detail->product_name, ' - ')) {
            $temp = explode(' - ', $order_detail->product_name);
            $product_name = trim($temp[0]);
            $product_attributes = explode(',', trim($temp[1]));
            $attr_html = '<br>';
            foreach ($product_attributes as $p_attr) {
                $attr_html .= '<span style="color:#565656">' . $p_attr . '</span>';
            }
            unset($product_attributes);
        } else {
            $product_name = $order_detail->product_name;
            $attr_html = '';
        }
        $p_temp = new Product($order_detail->product_id);
        $image_combination = $p_temp->getCombinationImages($this->context->language->id);
        if (isset($image_combination[$order_detail->product_attribute_id][0]['id_image'])) {
            $id_image = $order_detail->product_id . '-' .
                $image_combination[$order_detail->product_attribute_id][0]['id_image'];
        } else {
            $get_cover_image = Product::getCover($order_detail->product_id);
            $id_image = $order_detail->product_id . '-' . $get_cover_image['id_image'];
        }

        $link_rewrite = $p_temp->link_rewrite[$this->context->language->id];
        $link_obj = new Link();
        if ((bool) Configuration::get('PS_SSL_ENABLED')) {
            $product_img_path = 'https://' . $link_obj->getImageLink($link_rewrite, $id_image);
        } else {
            $product_img_path = 'http://' . $link_obj->getImageLink($link_rewrite, $id_image);
        }
        $product_link = $this->context->link->getProductLink($order_detail->product_id);
        unset($order_detail);
        unset($p_temp);
        unset($link_obj);
        $html = '';
        $html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin:0"><tbody><tr>
			<td width="20%" valign="top" align="center" style="padding:5px 0 0 0;margin:0;text-align:center;
            color:#565656">' .
            $this->getModuleTranslationByLanguage('returnmanager', 'IMAGE', 'common', $language) . '</td>
			<td width="35%" valign="top" align="center" style="padding:5px 0 0 0;margin:0;text-align:center;
            color:#565656">' .
            $this->getModuleTranslationByLanguage('returnmanager', 'ITEM', 'common', $language) . '</td>
			<td width="7%" valign="top" align="center" style="padding:5px 0 0 0;margin:0;text-align:center;
            color:#565656">' .
            $this->getModuleTranslationByLanguage('returnmanager', 'QTY', 'common', $language) . '</td>
			<td width="35%" valign="top" align="center" style="padding:5px 0 0 10px;margin:0;text-align:right;
            color:#565656">' .
            $this->getModuleTranslationByLanguage('returnmanager', 'PRICE', 'common', $language) . '
			</td></tr><tr><td colspan="4" align="left" valign="top">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tbody><tr>
			<td valign="middle" align="left" style="border-bottom:solid 2px #565656;width:90%">&nbsp;</td>
			</tr><tr><td valign="middle" align="left">&nbsp;</td></tr></tbody></table></td></tr><tr>
			<td width="20%"><div style="margin-bottom:5px">
			<a href="' . $product_link . '" target="_blank">
			<img src="' . $product_img_path . '" border="0" height="100" width="100"></a></div></td>
			<td valign="top" align="center" style="border-bottom:1px solid #f2f2f2;padding:12px 10px;margin:0">
			<p style="padding:0;margin:0">
			<a style="text-decoration:none;color:#565656" href="' . $product_link . '" target="_blank">
			<span style="color:#565656"><b>' . $product_name . '</b></span>' . $attr_html . '</a>
			</p></td><td valign="top" align="center" style="border-bottom:1px solid #f2f2f2;padding:12px 0;
        margin:0;text-align:center">
			<p style="padding:0 10px 0 0;margin:0">' . (int) $item_data['quantity'] . '</p></td>
			<td valign="top" align="center" style="border-bottom:1px solid #f2f2f2;padding:12px 0 0 10px;
        margin:0;text-align:right">
			<p style="white-space:nowrap;padding:0;margin:0;font-weight:bold">' . $price .
            '</p></td></tr></tbody></table>';
        return $html;
    }

    protected function getTemplateBaseHtml()
    {
        $template_html = array();
        $template_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
			<html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
            <title>Message from {shop_name}</title>
            <style>	@media only screen and (max-width: 300px){
                body {
                    width:218px !important;
                    margin:auto !important;
                }
                .table {width:195px !important;margin:auto !important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer{width:auto !important;
                display: block !important;}
                span.title{font-size:20px !important;line-height: 23px !important}
                span.subtitle{font-size: 14px !important;line-height: 18px !important;padding-top:10px !important;
                display:block !important;}
                td.box p{font-size: 12px !important;font-weight: bold !important;}
                .table-recap table, .table-recap thead, .table-recap tbody, .table-recap th,
                .table-recap td, .table-recap tr {
                    display: block !important;
                }
                .table-recap{width: 200px!important;}
                .table-recap tr td, .conf_body td{text-align:center !important;}
                .address{display: block !important;margin-bottom: 10px !important;}
                .space_address{display: none !important;}
            }
            @media only screen and (min-width: 301px) and (max-width: 500px) {
                body {width:308px!important;margin:auto!important;}
                .table {width:285px!important;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer{width:auto!important;
                display: block!important;}
                .table-recap table, .table-recap thead, .table-recap tbody, .table-recap th, .table-recap td,
                .table-recap tr {
                    display: block !important;
                }
                .table-recap{width: 295px !important;}
                .table-recap tr td, .conf_body td{text-align:center !important;}
				table.menu-header-mail{font-size:10px;}/*Agregado*/
				table.menu-header-mail tr td {padding:5px 5px!important;}/*Agregado*/
				table.footer-mail a, table.footer-mail p{font-size:10px;}/*Agregado*/
				table.footer-mail p{margin:0px;}/*Agregado*/
				table.footer-mail img.slogan{height:25px!important;}/*Agregado*/
            }
            @media only screen and (min-width: 501px) and (max-width: 768px) {
                body {width:478px!important;margin:auto!important;}
                .table {width:450px!important;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer{width:auto!important;
                display: block!important;}
            }
            @media only screen and (max-device-width: 480px) {
                body {width:308px!important;margin:auto!important;}
                .table {width:285px;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer{width:auto!important;
                display: block!important;}

                .table-recap{width: 295px!important;}
                .table-recap tr td, .conf_body td{text-align:center!important;}
                .address{display: block !important;margin-bottom: 10px !important;}
                .space_address{display: none !important;}
				
				
            }
			</style>

            </head>
            <body style="-webkit-text-size-adjust:none;background-color:#fff;width:650px;
            font-family:Open-sans,sans-serif;
             color:#555454;font-size:13px;line-height:18px;margin:auto">
            <table class="table table-mail" style="width:100%;margin-top:10px;-moz-box-shadow:0 0 5px #afafaf;
             -webkit-box-shadow:0 0 5px #afafaf;-o-box-shadow:0 0 5px #afafaf;box-shadow:0 0 5px #afafaf;
             filter:progid:DXImageTransform.Microsoft.Shadow(color=#afafaf,Direction=134,Strength=5)">
            <tr>
                <td align="center" style="padding:7px 0">
                    <table class="table" style="width:100%">
                        <tr>
                            <td align="center" class="logo" /* style="border-bottom:4px solid #333333; */padding:7px 0">
                                <a title="{shop_name}" href="{shop_url}" style="color:#337ff1">
                                  <img  style="max-height:70px;" src="{shop_logo}" alt="{shop_name}" />
                                </a>
                            </td>
                        </tr>
                        <tr>
						<tr>
						<td align="center" style="border-bottom:4px solid #333333">
							<!-------------------  MENU HEADER MAIL ---------------------- -->
							<table class="menu-header-mail">
								<tr align="center">
									<td style="padding:10px 15px;">
										<a style="text-decoration:none; color:black;" href="{shop_url}8-dresses">DRESSES</a>
									</td>
									<td style="padding:10px 15px;">
										<a style="text-decoration:none; color:black;" href="{shop_url}4-tops">TOPS</a>
									</td>
									<td style="padding:10px 15px;">
										<a style="text-decoration:none; color:black;" href="{shop_url}13-bottoms">BOTTOMS</a>
									</td style="padding:10px 15px;">
									<!--<td>
										<a style="text-decoration:none; color:black;" href="{shop_url}/gift-card">GIFT CARD</a>
									</td>-->
									<td style="padding:10px 15px;">
										<a style="text-decoration:none; color:black;" href="{shop_url}12-accesories">ACCESORIES</a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<!-- --------------------------- End Header MAIL ---------------------- -->
                            {template_content}
                        </tr>
                    </table>
                </td>
            </tr>
            </table>
			<!----------------------------- Start Footer ------------------------>
				<table align="center" class="footer-mail" style="width:100%;margin-top:10px;" >
					<tr><td>
					<tr>
						<td align="center" class="footer" style="padding:7px 0;">
						<!-- <span><a href="{shop_url}" style="color:#337ff1">{shop_name}</a> powered by <a href="http://www.prestashop.com/" style="color:#337ff1">PrestaShop&trade;</a></span>-->
						<img class="slogan" style="height:30px" src="{shop_url}mails/mail_element/slogan.png" alt="Live the Azai Experience" />
						</td>
					</tr>
					
					<tr>
						<td align="center">
							<table>
								<tr>
									<td>
										<a style="padding:0px 5px" href="https://www.facebook.com/azaistore"><img style="height:30px" src="{shop_url}mails/mail_element/facebook.png" /></a>
									</td>
									<td>
										<a href="https://twitter.com/azaistore" style="padding:0px 5px" ><img style="height:30px" src="{shop_url}mails/mail_element/instagram.png" /></a>
										</td>
									<td>
										<a href="https://www.instagram.com/azaistore/" style="padding:0px 5px" ><img style="height:30px" src="{shop_url}mails/mail_element/twitter.png" /></a>
										</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center" class="footer" style="padding:0px 7px;">
							<table width="300"">
								<tr>
									<td>
										<p style="text-align:center">AZAI Store ,  275 Miracle Mile, Coral Gables, FL 33134, EE.UU.</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center">
							<table style="font-size:10px">
								<tr>
									<td>
										<a href="{shop_url}content/3-terms-and-conditions-of-use" alt="" style="padding:0px 5px;color:gray;" >ABOUT US</a>
									</td>
									<td>
										<a href="{shop_url}content/2-privacy-policies" alt="" style="padding:0px 5px;color:gray;" >PRIVACY POLICY</a>
									</td>
									<td>
										<a href="{shop_url}content/6-shopping-changes-and-returns" alt="" style="padding:0px 5px;color:gray;" >RETURN POLICY</a>
									</td>
									<td>
										<a href="{shop_url}content/1-shipping-and-delivery" alt="" style="padding:0px 5px;color:gray;" >SHIPPING INFORMATION</a>
									</td>
									
								</tr>
							</table>
						</td>
					</tr>

					</td></tr>
				</table>
				<!----------------------------- End Footer ------------------------>
            </body>
			</html>';
        return $template_html;
    }

    protected function getDefaultNewReturnCustEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none" width="600"
            cellpadding="0" cellspacing="0">
			<tbody>
            <tr>
                <td colspan="9" align="center" valign="top">
                    <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
                        <tbody>
            <tr>
                <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                    <span>
                        <p style="padding:0 0 0 0;margin:0;font-size:16px;
                        font-weight:bold"> Dear {customer_full_name}, </p>
                        <p style="padding:20px 0 0 0;margin:0;color:#565656;
                        line-height:20px">Greetings from {shop_name}!</p>
                    </span>
                    <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                        Your return request has been received for the following item in your order
                        <span style="color:#00648b"> <a href="{order_history_link}"
                        target="_blank">{order_reference}</a></span>.
                    </p>
                    <span>
                        <p style="padding:5px 10px;background-color:#fffed5;border:1px solid #f9e2b2;color:#565656;
                         margin:10px 0 0 0;text-align:left;line-height:20px">
                            Please keep in mind that this is only the first step of the return process.
                             The request has to be approved by shop owner in order to further process the request.
                             You will be notified once the shop owner approves this return request.<br>
                        </p>
                    </span>
                </td>
            </tr>
                            <tr>
                                <td align="left" valign="top" style="padding:20px 20px 0 20px">
                                    {item_details}
                                </td>
                            </tr>
            <tr>
                <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                    <p style="margin:0;color:#565656;line-height:20px">
                        <b>Return id for this request is : #{return_id}</b>
                    </p>

                    <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                        We apologize for any inconvenience caused to you.
                    </p><br>
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="padding:15px 40px;margin:0;text-align:center;
                background-color:#f9f9f9" bgcolor="F9F9F9">
                <p style="padding:0;margin:0 0 7px 0">
                    <a title="{shop_name}" style="text-decoration:none;color:#565656"
                     href="{shop_url}" target="_blank"><span style="color:#565656">{shop_name}</span></a>
                </p><span>
                <p style="padding:10px 0 0 0;margin:0;border-top:solid 1px #cccccc;font-size:11px;color:#565656">
                    /* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */
                </p>
                </span></td>
            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
			</tbody>
            </table>';
        $template_html['subject'] = 'New Return Request has been received!';
        $template_html['text_content'] = 'Dear {customer_full_name},Greetings from
            {shop_name}!Your return request has been received for
			the following item in your order  {order_reference}.Please keep in
            mind that this is only the first step of the return
			process. The request has to be approved by shop owner in order to
            further process the request. You will be notified once
			the shop owner approves this return request.{item_details}Return id for this
			request is : #{return_id}We apologizefor any inconvenience caused to
			you.{shop_name}/* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */';
        return $template_html;
    }

    protected function getDefaultNewReturnAdmEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none"
            width="600" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td colspan="9" align="center" valign="top">
            <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <span>
                                <p style="padding:0 0 0 0;margin:0;font-size:16px;font-weight:bold"> Hey Admin, </p>
                            </span>
                            <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                                A new return request has been received against the order {order_reference}</span>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="padding:20px 20px 0 20px">
                            Item to be returned <br><br>{item_details}
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <p style="margin:0;color:#565656;line-height:20px">
                                <b>Return id for this request is : #{return_id}</b>
                            </p>
                        </td>
                    </tr>
                    <tr>
                    <td>
                    <span>
                        <p style="padding:5px 10px;background-color:#fffed5;border:1px solid #f9e2b2;
                        color:#565656;
                         margin:10px 0 0 0;text-align:left;line-height:20px">
                            Please login to the admin panel and take appropriate action regarding this return request.
                            This mail is just to notify you about the return request,
                            you can process the return request only from back office.<br>
                        </p>
                    </span>
                    </td>
                    </tr>
                </tbody>
            </table>
                    </td>
                </tr>
            </tbody>
            </table>';
        $template_html['subject'] = 'Return requested by a Customer!';
        $template_html['text_content'] = 'Hey Admin,A new return request has been received against the order
            {order_reference}.Item
			to be returned {item_details}Return id for this request is : #{return_id}Click here to go to the admin panel
            and take
			appropriate action regarding this return request. This mail is just to notify you about the return request,
            you can
			process the return request only from back office.';
        return $template_html;
    }

    protected function getDefaultReturnApprovedEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none;"
            width="600" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td colspan="9" align="center" valign="top">
                        <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                        <span>
                            <p style="padding:0 0 0 0;margin:0;font-size:16px;
                            font-weight:bold"> Dear {customer_full_name}, </p>
                            <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                            Greetings from {shop_name}!</p>
                        </span>
                        <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                        Your return request has been approved by the store owner for the following item in your order
                         <span style="color:#00648b"> <a href="{order_history_link}" target="_blank">{order_reference}
                         </a></span>.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="left" valign="top" style="padding:20px 20px 0 20px">
                        {item_details}
                    </td>
                </tr>
                <tr>
                    <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                        <p style="margin:0;color:#565656;line-height:20px">
                            <b>Return id for this request is : #{return_id}</b>
                        </p>

                        <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                            The store owner will now take further actions on this return request.
                             You can track the status of your return request in the return section of our store.
                        </p>

                        {attachment_text}

                        <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                            We apologize for any inconvenience caused to you.
                        </p><br>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" style="padding:15px 40px;margin:0;text-align:center;
                    background-color:#f9f9f9" bgcolor="F9F9F9">
                    <p style="padding:0;margin:0 0 7px 0">
                        <a title="{shop_name}" style="text-decoration:none;color:#565656"
                         href="{shop_url}" target="_blank"><span style="color:#565656">{shop_name}</span></a>
                    </p><span>
                    <p style="padding:10px 0 0 0;margin:0;border-top:solid 1px #cccccc;font-size:11px;color:#565656">
                        /* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */
                    </p>
                    </span></td>
                </tr>
            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
            </table>';
        $template_html['subject'] = 'Your Return request is Approved!';
        $template_html['text_content'] = 'Dear {customer_full_name},Greetings from {shop_name}!Your return request
			has been approved by the store owner for the following item in your order  {order_reference}.
            {item_details}Return id
			for this request is : #{return_id}The store owner will now take further actions on this return request.
            You can
			track the status of your return request in the return section of our store.We apologize for any
			inconvenience caused to you.{shop_name}24x7 Customer Support &bull;
			Flexible Payment Options &bull; Largest Collection &bull; Easy Returns';
        return $template_html;
    }

    protected function getDefaultReturnDeniedEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none;"
            width="600" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td colspan="9" align="center" valign="top">
                <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <span>
                                <p style="padding:0 0 0 0;margin:0;font-size:16px;font-weight:bold">
                                Dear {customer_full_name}, </p>
                                <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                                Greetings from {shop_name}!</p>
                            </span>
                            <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                                We are sorry to inform you that your return request for the following
                                 item in your order <span style="color:#00648b">
                                  <a href="{order_history_link}" target="_blank">{order_reference}</a>
                                   </span> has been denied by the store owner.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="padding:20px 20px 0 20px">
                            {item_details}
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <p style="margin:0;color:#565656;line-height:20px">
                                <b>Return id for this request is : #{return_id}</b>
                            </p>

                            <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                                We apologize for any inconvenience caused to you.
                            </p><br>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top" style="padding:15px 40px;margin:0;text-align:center;
                    background-color:#f9f9f9" bgcolor="F9F9F9">
                        <p style="padding:0;margin:0 0 7px 0">
                            <a title="{shop_name}" style="text-decoration:none;color:#565656"
                             href="{shop_url}" target="_blank"><span style="color:#565656">{shop_name}</span></a>
                        </p><span>
                        <p style="padding:10px 0 0 0;margin:0;border-top:solid 1px #cccccc;font-size:11px;
                    color:#565656">
                            /* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */
                        </p>
                        </span></td>
                    </tr>
                </tbody>
            </table>
            </td>
            </tr>
        </tbody>
        </table>';
        $template_html['subject'] = 'Your return request is Dis-apporved!';
        $template_html['text_content'] = 'Dear {customer_full_name},Greetings from {shop_name}!
            We are sorry to inform you that your
			return request for the following item in your order
            {order_reference} has been denied by the store owner.{item_details}Return
			id for this request is : #{return_id}We apologize for any inconvenience caused to you.{shop_name}24x7
			Customer Support &bull; Flexible Payment Options &bull; Largest Collection &bull; Easy Returns';
        return $template_html;
    }

    protected function getDefaultReturnStatusEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none;" width="600"
            cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
            <td colspan="9" align="center" valign="top">
            <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <span>
                                <p style="padding:0 0 0 0;margin:0;font-size:16px;
                                font-weight:bold"> Dear {customer_full_name}, </p>
                                <p style="padding:20px 0 0 0;margin:0;color:#565656;
                                line-height:20px">Greetings from {shop_name}!</p>
                            </span>
                            <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                                We are pleased to inform you that status of your return request
                                 for the following item in your order
                                  <span style="color:#00648b"> <a href="{order_history_link}"
                                   target="_blank">{order_reference}</a></span> has been updated by the
                                    store owner.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="padding:20px 20px 0 20px">
                            {item_details}
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                            <p style="margin:0;color:#565656;line-height:20px">
                                <b>Return id for this request is : #{return_id}</b>
                            </p>

                            <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                                Return Status has been changed from <b>{previous_status}</b> to <b>{current_status}
                                </b>.
                            </p>

                            <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                                To know more about the return request you have to <span style="color:#00648b">
                                 <a href="{signin_link}" target="_blank">login</a>
                                 </span> to our store and go to the returns
                                  section of our store.
                            </p><br>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top" style="padding:15px 40px;
                        margin:0;text-align:center;background-color:#f9f9f9" bgcolor="F9F9F9">
                        <p style="padding:0;margin:0 0 7px 0">
                            <a title="{shop_name}" style="text-decoration:none;color:#565656"
                             href="{shop_url}" target="_blank"><span style="color:#565656">{shop_name}</span></a>
                        </p><span>
                        <p style="padding:10px 0 0 0;margin:0;
                        border-top:solid 1px #cccccc;font-size:11px;color:#565656">
                            /* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */
                        </p>
                        </span></td>
                    </tr>
                </tbody>
            </table>
            </td>
            </tr>
            </tbody>
            </table>';
        $template_html['subject'] = 'Your Return Request status has been updated!';
        $template_html['text_content'] = 'Dear {customer_full_name},Greetings from {shop_name}!We are pleased
            to inform you that status
			of your return request for the following item in your order  {order_reference} has been updated by the store
			owner.{item_details}Return id for this request is : #{return_id}Return Status has been changed from
            {previous_status}
			to {current_status}.To know more about the return request you have to login to our store and
            go to the returns section of our
			store.{shop_name}24x7 Customer Support &bull; Flexible Payment Options &bull;
            Largest Collection &bull; Easy Returns';
        return $template_html;
    }

    protected function getDefaultReturnCompletedEmail()
    {
        $template_html = array();
        $template_html['body'] = '<table style="width:640px;border:0px none" width="600"
            cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td colspan="9" align="center" valign="top">
                        <table width="100%" style="border:1px solid #e6e6e6;width:100%" cellpadding="0" cellspacing="0">
                            <tbody>
            <tr>
                <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                    <span>
                        <p style="padding:0 0 0 0;margin:0;
                        font-size:16px;font-weight:bold"> Dear {customer_full_name}, </p>
                        <p style="padding:20px 0 0 0;
                        margin:0;color:#565656;line-height:20px">Greetings from {shop_name}!</p>
                    </span>
                    <p style="padding:20px 0 0 0;margin:0;color:#565656;line-height:20px">
                        We are pleased to inform you that your return request for the following item in your order
                         <span style="color:#00648b"> <a href="{order_history_link}"
                         target="_blank">{order_reference}</a>
                          </span> has been marked completed by the store owner.
                    </p>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" style="padding:20px 20px 0 20px">
                    {item_details}
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" style="margin:0;padding:25px 20px 5px 25px" bgcolor="FFFFFF">
                    <p style="margin:0;color:#565656;line-height:20px">
                        <b>Return id for this request is : #{return_id}</b>
                    </p>

                    <p style="padding:19px 0 0 0;margin:0;color:#565656;line-height:19px">
                        To know more about the return request you need
                         <span style="color:#00648b"> <a href="{signin_link}" target="_blank">login</a></span>
                          to our store and go to the returns section of our store.
                    </p><br>
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="padding:15px 40px;margin:0;text-align:center;
                background-color:#f9f9f9" bgcolor="F9F9F9">
                <p style="padding:0;margin:0 0 7px 0">
                    <a title="{shop_name}" style="text-decoration:none;color:#565656"
                     href="{shop_url}" target="_blank"><span style="color:#565656">{shop_name}</span></a>
                </p><span>
                <p style="padding:10px 0 0 0;margin:0;border-top:solid 1px #cccccc;font-size:11px;color:#565656">
                    /* 24x7 Customer Support � Flexible Payment Options � Largest Collection � Easy Returns */
                </p>
                </span></td>
            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>';
        $template_html['subject'] = 'Your Return Request has been completed!';
        $template_html['text_content'] = 'Dear {customer_full_name},Greetings from {shop_name}!We are pleased to inform
			you that your return request for the following item in your order
            {order_reference} has been marked completed
			by the store owner.{item_details}Return id for this request is : #{return_id}To know more about
			the return request you need  login to our store and go to the returns section of our
			store.{shop_name}24x7 Customer Support &bull; Flexible Payment Options &bull; Largest Collection &bull;
            Easy Returns';
        return $template_html;
    }

    public function getDefaultReturnGuidelines()
    {
        $brace_comment1 = '(it can be found in Return Slip for this Return Request)';
        $html = '<p><strong>Additional Instructions for mailing your package</strong></p>
			<ul>
			<li>Securely pack the items in a box.</li>
			<li>Paste the mailing label on the address side of your package.</li>
			<li>Remember to include the Return Authorization Label ' . $brace_comment1 . '.</li>
			<li>Make sure that the address that you are using to post the package is correct and ' .
            'matches the address present in the Return Slip.</li>
			<li>Please make sure that the Return Package
			 contains all the item that you have received with there accessories (if any)
             related to this return request.</li>
			<li>Ship package from your nearest post office or courier company of your choice.</li>
			<li>The return request will be further processed once we recive and verify the package sent.</li>
			</ul>';
        return $html;
    }

    public function getDefaultPolicy($product_id, $category_id)
    {
        $get_policy_id = 'select rd.return_data_id from ' . _DB_PREFIX_ . 'velsof_return_data as
		rd, ' . _DB_PREFIX_ . 'velsof_return_policy_product as
		rpp,' . _DB_PREFIX_ . 'velsof_return_data_lang as rdl where
		rd.return_data_id = rpp.return_data_id and rd.return_data_id = rdl.return_data_id
        and rd.policy = 1 AND rd.active = 1 AND
		rpp.id_categories = ' . (int) $category_id . ' and rdl.id_shop=' . (int) $this->context->shop->id;
        $policy_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_policy_id);
        $settings = Tools::unSerialize(Configuration::get('VELSOF_RETURNMANAGER'));
        $exceptional_product = array();
        $exceptional_category = array();
        if (isset($settings['policy']['ex_product']) && $settings['policy']['ex_product'] != '') {
            $exceptional_product = explode(',', $settings['policy']['ex_product']);
        }
        if (isset($settings['policy']['ex_category']) && $settings['policy']['ex_category'] != '') {
            $exceptional_category = explode(',', $settings['policy']['ex_category']);
        }
        if ($policy_id && is_array($policy_id)) {
            $return_data_id = $policy_id['return_data_id'];
            if (in_array($product_id, $exceptional_product)) {
                $return_data_id = -1;
            } elseif (in_array($category_id, $exceptional_category)) {
                $return_data_id = -1;
            } elseif ($policy_id['return_data_id'] == 0) {
                $return_data_id = -1;
            }
        } else {
            $get_no_policy = 'select return_data_id from ' . _DB_PREFIX_ . 'velsof_return_policy_product as
			rpp where rpp.id_categories = ' . (int) $category_id;
            $no_policy_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($get_no_policy);
            if (in_array($product_id, $exceptional_product)) {
                $return_data_id = -1;
            } elseif (in_array($category_id, $exceptional_category)) {
                $return_data_id = -1;
            } elseif (isset($no_policy_id['return_data_id']) && $no_policy_id['return_data_id'] == 0) {
                $return_data_id = -1;
            } elseif (isset($settings['policy']['default']) && $settings['policy']['default'] != 0) {
                $return_data_id = $settings['policy']['default'];
            } else {
                $return_data_id = -1;
            }
        }
        return $return_data_id;
    }
    
    public function leftTextTransaltions()
    {
        //left over transaltions
        $this->l('ReturnSlip', 'common');
        $this->l('Return Authorization Label', 'common');
        $this->l('Return Mailing Label', 'common');
        $this->l('Cut this label and affix to the outside of your return package.', 'common');
        $this->l('FROM', 'common');
        $this->l('Return Id', 'common');
        $this->l('If you do not want to use the above mailing label, you can send your return package using a carrier of your choice to the following address. You will need to pay for return postage costs.', 'common');
        $this->l('Return Authorization Label', 'common');
        $this->l('Cut this and place inside the return package with your name and signature at the bottom.', 'common');
        $this->l('Return Id', 'common');
        $this->l('Order', 'common');
        $this->l('Item Description', 'common');
        $this->l('Total Price', 'common');
        $this->l('Quantity', 'common');
        $this->l('TO WHOM IT MAY CONCERN', 'common');
        $this->l('I hereby declare that this return package contains all the items with there accessories', 'common');
        $this->l('(if any) related to this return request with', 'common');
        $this->l('Return Id', 'common');
        $this->l('against', 'common');
        $this->l('Order', 'common');
        $this->l('I also declare that the items in this package are as it is and are not tempered with.', 'common');
        $this->l('You can reject the return request if anything like this is found.', 'common');
        $this->l('Your Sincerly', 'common');
        $this->l('Please find in attachements the return slip for this return request.', 'common');
        $this->l('IMAGE', 'common');
        $this->l('ITEM', 'common');
        $this->l('QTY', 'common');
        $this->l('PRICE', 'common');
        $this->l('Phone', 'common');
    }
    
    public function getModuleTranslationByLanguage($module, $string, $source, $language, $sprintf = null, $js = false)
    {
        $modules = array();
        $langadm = array();

        $translations_merged = array();
        $name = $module instanceof Module ? $module->name : $module;
        if (!isset($translations_merged[$name]) && isset(Context::getContext()->language)) {
            $files_by_priority = array(
                _PS_MODULE_DIR_ . $name . '/translations/' . $language . '.php'
            );

            foreach ($files_by_priority as $file) {
                if (file_exists($file)) {
                    include($file);
                    /* No need to define $_MODULE as it is defined in the above included file. */
                    $modules = $_MODULE;
                    $translations_merged[$name] = true;
                }
            }
        }

        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);

        if ($modules == null) {
            if ($sprintf !== null) {
                $string = Translate::checkAndReplaceArgs($string, $sprintf);
            }

            return str_replace('"', '&quot;', $string);
        }

        $current_key = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
        $default_key = Tools::strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;

        if ('controller' == Tools::substr($source, -10, 10)) {
            $file = Tools::substr($source, 0, -10);
            $current_key_file = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $file) . '_' . $key;
            $default_key_file = Tools::strtolower('<{' . $name . '}prestashop>' . $file) . '_' . $key;
        }

        if (isset($current_key_file) && !empty($modules[$current_key_file])) {
            $ret = Tools::stripslashes($modules[$current_key_file]);
        } elseif (isset($default_key_file) && !empty($modules[$default_key_file])) {
            $ret = Tools::stripslashes($modules[$default_key_file]);
        } elseif (!empty($modules[$current_key])) {
            $ret = Tools::stripslashes($modules[$current_key]);
        } elseif (!empty($modules[$default_key])) {
            $ret = Tools::stripslashes($modules[$default_key]);
            // if translation was not found in module, look for it in AdminController or Helpers
        } elseif (!empty($langadm)) {
            $ret = Tools::stripslashes(Translate::getGenericAdminTranslation($string, $key, $langadm));
        } else {
            $ret = Tools::stripslashes($string);
        }

        if ($sprintf !== null) {
            $ret = Translate::checkAndReplaceArgs($ret, $sprintf);
        }

        if ($js) {
            $ret = addslashes($ret);
        } else {
            $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
        }
        return $ret;
    }
    
    public function getIsoCode($return_id)
    {
        $query = 'Select id_lang from '._DB_PREFIX_.'velsof_rm_order where id_rm_order = '.(int)$return_id;
        $language_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        $isoCode = Language::getIsoById($language_id);
        if (!$isoCode) {
            $isoCode = Language::getIsoById($this->context->language->id);
        }
        return $isoCode;
    }
}
