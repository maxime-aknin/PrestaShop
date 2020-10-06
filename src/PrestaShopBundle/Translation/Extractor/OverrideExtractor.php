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

namespace PrestaShopBundle\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Dumper\XliffFileDumper;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Extract all theme translations from php files in override folder.
 */
class OverrideExtractor
{
	private $phpExtractor;
	private $dumpers = [];
	/**
	 * @var \Symfony\Component\Translation\MessageCatalogue
	 */
	private $catalog;
	/** @var \PrestaShopBundle\Translation\Provider\OthersProvider $overrideProvider */
	private $overrideProvider;
	private $outputPath;
	public $overrideDirectory;
	private $format = 'xlf';

	public function __construct(PhpExtractor $phpExtractor)
	{
		$this->phpExtractor = $phpExtractor;
		$this->dumpers[] = new XliffFileDumper();
	}

	/**
	 * @param \PrestaShopBundle\Translation\Provider\OverrideProvider $overrideProvider
	 * @return \PrestaShopBundle\Translation\Extractor\OverrideExtractor
	 */
	public function setOverrideProvider($overrideProvider)
	{
		$this->overrideProvider = $overrideProvider;

		return $this;
	}

	/**
	 * @param string $locale
	 * @return mixed
	 */
	public function extract($locale = 'en-US')
	{
		$unfiltered_catalog = new MessageCatalogue($locale);
		$this->phpExtractor->extract($this->overrideDirectory, $unfiltered_catalog);

		$this->catalog = new MessageCatalogue($locale);
		foreach($unfiltered_catalog->getDomains() as $domain) {
			foreach ($this->overrideProvider->getFilters() as $reg) {
				if(preg_match($reg, $domain)) {
					foreach ($unfiltered_catalog->all($domain) as $id => $translation) {
						$domain = str_replace('.', '', $domain);
						$this->catalog->set($id, $translation, $domain);
					}
				}
			}
		}

		$options = [];
		if (!empty($this->catalog->all())) {
			foreach ($this->dumpers as $dumper) {
				if ($this->format === $dumper->getExtension()) {
					if (null !== $this->outputPath) {
						$options['path'] = $this->outputPath;
					}
					// dump to $lang
					$dumper->dump($this->catalog, $options);
					// then dump to default for getDefaultCatalog()
					$dumper->setRelativePathTemplate('default/%domain%.%extension%');
					return $dumper->dump($this->catalog, $options);
				}
			}
		}

		throw new \LogicException(sprintf('The format %s is not supported.', $this->format));
	}


	public function setOutputPath($outputPath)
	{
		$this->outputPath = $outputPath;

		return $this;
	}

}
