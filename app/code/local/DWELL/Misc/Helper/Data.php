<?php
/**
 * Dwell - adding config fetch for MyRegistry.com integration on product page.
 *
 */
class DWELL_Misc_Helper_Data extends Mage_Catalog_Helper_Data
{
    /**
     * Config path
     */
    const DWELL_TOPBAR_MESSAGE_ENABLED = 'topbar/message/enabled';
    const DWELL_TOPBAR_MESSAGE_VALUE = 'topbar/message/value';
    const DWELL_NEWSLETTER_OFFER_TEXT = 'newsletter_text/message/value';
    const DWELL_NEWSLETTER_EMAIL_SIGNUP_TEXT = 'newsletter_text/email_message/value';
    const DWELL_NEWSLETTER_EMAIL_SIGNUP_TEXT2 = 'newsletter_text/email_message/value2';
    const DWELL_HOMEPAGE_TEXT_BELOW_CAROUSEL = 'homepage_text/message/value';


    /**
     * Retrieve Registry enabled
     *
     * @return bool
     */
    public function topBarMessageEnabled()
    {
        return Mage::getStoreConfig(self::DWELL_TOPBAR_MESSAGE_ENABLED);
    }
    
    public function getTopBarMessage() 
    {
    	return Mage::getStoreConfig(self::DWELL_TOPBAR_MESSAGE_VALUE);
    }
    
    /**
     * Retrieve Newsletter Footer text
     * @return text
     */
    public function getNewsletterOfferMessage(){
        return Mage::getStoreConfig(self::DWELL_NEWSLETTER_OFFER_TEXT);
    }
    
    /**
     * Retrieve Newsletter Footer text after email signup
     * @return text
     */
    public function getNewsletterMessageForEmailSignup(){
        return Mage::getStoreConfig(self::DWELL_NEWSLETTER_EMAIL_SIGNUP_TEXT);
    }
    
    /**
     * Retrieve Newsletter Footer text after email signup Line 2
     * @return text
     */
    public function getNewsletterMessageForEmailSignupLine2(){
        return Mage::getStoreConfig(self::DWELL_NEWSLETTER_EMAIL_SIGNUP_TEXT2);
    }
    
    /**
     * Retrieve Homepage texts below Banner slider carousel
     */
    public function getHomePageTextBelowCarousel(){
        return Mage::getStoreConfig(self::DWELL_HOMEPAGE_TEXT_BELOW_CAROUSEL);
    }
}
