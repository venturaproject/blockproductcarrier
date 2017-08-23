{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



{if $data_carriers}
<div class="product-carriers">
{$data_carriers_nb}
{foreach from=$data_carriers item=data_carrier name=data_carrier}
<div class="data_carriers">
<p>{if $CARRIER_IMG}
{if $data_carrier.img}
<img src="{$img_ship_dir}{$data_carrier.id_carrier_reference}.jpg" width="{$CARRIER_IMG_WIDTH}" height="{$CARRIER_IMG_HEIGHT}" alt="{$data_carrier.name} " />
{else}
<i class="icon-truck icon-2x"></i>
{/if}
{/if}
{if $CARRIER_NAME}<span class="carrier_name">{$data_carrier.name}</span>{/if} {if $CARRIER_DELAY}<span class="carrier_delay">{$data_carrier.delay}</span>{/if}
{if $data_carrier.is_free}<span class="carrier_is_free">{l s='Free' mod='blockproductcarrier'}</span>{/if}
</div>
{/foreach}
</div>
{/if}
