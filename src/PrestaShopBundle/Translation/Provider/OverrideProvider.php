<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Translation\Provider;

use Symfony\Component\Translation\MessageCatalogue;

class OverrideProvider extends AbstractProvider implements UseDefaultCatalogueInterface
{
	/** @var \PrestaShopBundle\Translation\Extractor\OverrideExtractor $overrideExtractor */
	public $overrideExtractor;

	/**
	 * {@inheritdoc}
	 */
	public function getTranslationDomains()
	{
		return [
			'^Override*',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilters()
	{
		return [
			'#^Override*#',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIdentifier()
	{
		return 'override';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultCatalogue($empty = true)
	{
		$defaultCatalogue = new MessageCatalogue($this->getLocale());

		foreach ($this->getFilters() as $filter) {
			$filteredCatalogue = $this->getCatalogueFromPaths(
				[$this->getDefaultResourceDirectory()],
				$this->getLocale(),
				$filter
			);
			$defaultCatalogue->addCatalogue($filteredCatalogue);
		}

		return $defaultCatalogue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultResourceDirectory()
	{
		return $this->resourceDirectory;
	}


	public function synchronizeOverride()
	{

		$path = $this->getDefaultResourceDirectory();
		// output xlf to app/Ressources/translations/default/
		// and  app/Ressources/translations/{$lang}
		$this
			->overrideExtractor
			->setOutputPath($path)
			->setOverrideProvider($this)
			->extract($this->getLocale())
		;
	}
}
