<?php
/*
 * (c) Copyright Spryker Systems GmbH 2015
 */

namespace Pyz\Zed\Cms\Business\Internal\DemoData;

use Generated\Shared\Transfer\PageTransfer;
use Pyz\Zed\Cms\Dependency\Facade\CmsToLocaleInterface;
use SprykerFeature\Zed\Cms\Business\Mapping\GlossaryKeyMappingManagerInterface;
use SprykerFeature\Zed\Cms\Business\Page\PageManagerInterface;
use SprykerFeature\Zed\Cms\Business\Template\TemplateManagerInterface;
use SprykerFeature\Zed\Cms\Dependency\Facade\CmsToGlossaryInterface;
use SprykerFeature\Zed\Cms\Dependency\Facade\CmsToUrlInterface;
use SprykerFeature\Zed\Installer\Business\Model\AbstractInstaller;

/**
 * Class CmsInstall
 *
 * @package Pyz\Zed\Cms\Business\Internal\DemoData
 */
class CmsInstall extends AbstractInstaller
{

    /**
     * @var CmsToGlossaryInterface
     */
    protected $glossaryFacade;

    /**
     * @var CmsToUrlInterface
     */
    protected $urlFacade;

    /**
     * @var CmsToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var GlossaryKeyMappingManagerInterface
     */
    protected $keyMappingManager;

    /**
     * @var array
     */
    protected $staticPages = [
        'imprint' => ['de_DE' => '/impressum'],
        'privacy' => ['de_DE' => '/datenschutz'],
        'terms'   => ['de_DE' => '/agb'],
    ];

    /**
     * @var string
     */
    protected $contentKey;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param CmsToGlossaryInterface             $glossaryFacade
     * @param CmsToUrlInterface                  $urlFacade
     * @param CmsToLocaleInterface               $localeFacade
     * @param TemplateManagerInterface           $templateManager
     * @param PageManagerInterface               $pageManager
     * @param GlossaryKeyMappingManagerInterface $keyMappingManager
     * @param string                             $filePath
     * @param string                             $contentKey
     * @param string                             $template
     * @param string                             $templateName
     */
    public function __construct(
        CmsToGlossaryInterface $glossaryFacade,
        CmsToUrlInterface $urlFacade,
        CmsToLocaleInterface $localeFacade,
        TemplateManagerInterface $templateManager,
        PageManagerInterface $pageManager,
        GlossaryKeyMappingManagerInterface $keyMappingManager,
        $filePath,
        $contentKey,
        $template,
        $templateName
    ) {
        $this->glossaryFacade = $glossaryFacade;
        $this->urlFacade = $urlFacade;
        $this->localeFacade = $localeFacade;
        $this->templateManager = $templateManager;
        $this->pageManager = $pageManager;
        $this->keyMappingManager = $keyMappingManager;

        $this->filePath = $filePath;

        $this->contentKey = $contentKey;

        $this->template = $template;
        $this->templateName = $templateName;
    }

    /**
     *
     */
    public function install()
    {
        $this->info("This will install a standard set of cms pages in the demo shop ");
        $this->installCmsData();
    }

    /**
     *
     */
    public function installCmsData()
    {
        foreach ($this->localeFacade->getAvailableLocales() as $locale) {
            $localePath = $this->filePath . '/' . $locale;
            if ($this->checkPathExists($localePath)) {
                $this->installStaticPagesFromPath($localePath, $locale);
            }
        }
    }

    /**
     * @param $localePath
     * @param $pageKey
     *
     * @return string
     */
    public function getFileName($localePath, $pageKey)
    {
        return $localePath . '/initial_' . $pageKey . '.html';
    }

    /**
     * @return mixed
     */
    private function createTemplate()
    {
        if ($this->templateManager->hasTemplatePath($this->template) === true) {
            return $this->templateManager->getTemplateByPath($this->template);
        }

        return $this->templateManager->createTemplate(
            $this->templateName,
            $this->template
        );
    }

    /**
     * @param $localePath
     *
     * @return bool
     */
    private function checkPathExists($localePath)
    {
        return is_dir($localePath);
    }

    /**
     * @param $content
     * @param $url
     */
    private function createPageForContent($content, $url)
    {
        if ($this->urlFacade->hasUrl($url) === true) {
            $this->warning(sprintf('Page with URL %s already exists. Skipping.', $url));

            return;
        }

        $templateTransfer = $this->createTemplate();
        $pageTransfer = $this->createPage($templateTransfer);
        $this->keyMappingManager->addPlaceholderText($pageTransfer, $this->contentKey, $content);
        $urlTransfer = $this->pageManager->createPageUrl($pageTransfer, $url);

        $this->pageManager->touchPageActive($pageTransfer);
        $this->urlFacade->touchUrlActive($urlTransfer->getIdUrl());
    }

    /**
     * @param $templateTransfer
     *
     * @return PageTransfer
     */
    private function createPage($templateTransfer)
    {
        $pageTransfer = new PageTransfer();
        $pageTransfer->setFkTemplate($templateTransfer->getIdCmsTemplate());
        $pageTransfer->setIsActive(true);

        return $this->pageManager->savePage($pageTransfer);
    }

    /**
     * @param $localePath
     * @param $locale
     */
    private function installStaticPagesFromPath($localePath, $locale)
    {
        foreach ($this->staticPages as $pageKey => $localeConfig) {
            $file = $this->getFileName($localePath, $pageKey);
            if ($fileContent = file_get_contents($file)) {
                $this->createPageForContent($fileContent, $localeConfig[$locale]);
            }
        }
    }
}
