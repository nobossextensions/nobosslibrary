<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

/*
 * Inicio da exibicao das tres notificacoes possiveis de exibirmos hoje ao usuario. Algumas observacoes:
 *      - Todas notificacoes possuem botoes de acao
 *      - Podemos ter casos de mais de uma mensagem ser exibida ao mesmo tempo e casos onde nenhuma seja exibida
 */

?>

<section class="license-section nb-lg-8 nb-md-10 nb-sm-12 nb-xs-12">
<?php


// Exibe mensagem que a licenca de atualizacoes expirou junto com opcao para renovar
if (!$this->inside_support_updates_expiration && !$this->has_parent_license){ 
?>
    <div data-id='license-alert-message' class='feedback-notice feedback-notice--error'>
        <span class="feedback-notice__icon fa fa-ban"></span>
        <div class="feedback-notice__content">
            <h4 class="feedback-notice__title">
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRED_LICENSE_TITLE'); ?>
            </h4>
            <p class="feedback-notice__message" data-id='license-alert-message-text'>
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRED_LICENSE_DESC'); ?>
                <?php if(!$this->has_parent_license){ ?>
                <a href="#" data-id="btn-renew-license">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRING_LICENSE_BUTTON'); ?>
                </a>
                <?php } ?>
            </p>
        </div>
    </div>
<?php
}
// Exibe mensagem de que a licenca para atualizacoes esta proxima de expirar e da opcao de renovar
else if ($this->updates_near_to_expire) { ?>
    <div data-id='license-alert-message' class='feedback-notice feedback-notice--warning'>
        <span class="feedback-notice__icon fa fa-ban"></span>
        <div class="feedback-notice__content">
            <h4 class="feedback-notice__title">
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRING_LICENSE_TITLE'); ?>
            </h4>
            <p class="feedback-notice__message" data-id='license-alert-message-text'>
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRING_LICENSE_DESC'); ?>
                <?php if(!$this->has_parent_license){ ?>
                <a href="#" data-id="btn-renew-license">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_EXPIRING_LICENSE_BUTTON'); ?>
                </a>
                <?php } ?>
            </p>
        </div>
    </div>
<?php 
}

// Exibe mensagem que o site atual nao esta autorizado junto a um botao que permite alterar
if (!$isAuthorizedUrl){
?>
    <div data-id='license-alert-message' class='feedback-notice feedback-notice--error'>
        <span class="feedback-notice__icon fa fa-ban"></span>
        <div class="feedback-notice__content">
            <h4 class="feedback-notice__title">
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_STATUS_TITLE'); ?>
            </h4>
            <p class="feedback-notice__message" data-id='license-alert-message-text'>
                <?php echo JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_UNAUTHORIZED_URL', $this->licenseInfoData->authorized_url); ?>
                <a href="#" data-id="btn-change-url">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_UNAUTHORIZED_URL_BUTTON'); ?>
                </a>
            </p>
        </div>
    </div>
    
<?php
}

// Exibe mensagem que a licenca esta com status despublicado
if($this->licenseInfoData->state == 0){ ?>
    <div data-id='license-alert-message' class='feedback-notice feedback-notice--warning'>
        <span class="feedback-notice__icon fa fa-exclamation-triangle"></span>
        <div class="feedback-notice__content">
            <h4 class="feedback-notice__title">
                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_STATUS_TITLE'); ?>
            </h4> 
             <p class="feedback-notice__message" data-id='license-alert-message-text'>
                <?php echo JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_UNPUBLISHED_LICENSE', $this->licenseInfoData->authorized_url); ?>
            </p>
        </div>
    </div>
    <?php
} 
else {
    // Exibe mensagem que ha um upgrade diponivel (usuario ja pagou e a nossa equipe ja atualizou o plano no sistema) junto um um botao para atualizacao da extensao
    if(!$this->license_has_errors && ($this->licenseInfoData->has_upgrade == 1)){ ?>
        <div data-id="plan-upgrade" class='feedback-notice feedback-notice--info'>
            <span class="feedback-notice__icon fa fa-info-circle"></span>
            <div class="feedback-notice__content">
                <h4 class="feedback-notice__title">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_INFO_TITLE'); ?>
                </h4>
                <p class="feedback-notice__message" data-id="plan-upgrade-text">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_HAS_UPGRADE_AVALIABLE_DOWNLOAD'); ?>
                    <a data-id="btn-plan-upgrade" target="" href="<?php echo JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'); ?>">
                        <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_HAS_UPGRADE_AVALIABLE_DOWNLOAD_BUTTON'); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php
    }
    // Exibe mensagem que ha planos melhores disponiveis para a extensao junto a um botao de upgrade
    else if($isAuthorizedUrl && ($this->licenseInfoData->has_higher_plan == 1) && !$this->has_parent_license){
        ?>
        <div data-id="plan-upgrade" class='feedback-notice feedback-notice--info'>
            <span class="feedback-notice__icon fa fa-info-circle"></span>
            <div class="feedback-notice__content">
                <h4 class="feedback-notice__title">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_HAS_HIGHER_PLAN_AVALIABLE_TITLE'); ?>
                </h4>
                <p class="feedback-notice__message" data-id="plan-upgrade-text">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_HAS_HIGHER_PLAN_AVALIABLE'); ?>
                    <a data-id="btn-plan-upgrade-buy" target="" href="<?php echo JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'); ?>">
                        <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_HAS_HIGHER_PLAN_BUTTON'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    // Nao tem erros a exibir, periodo de atualizacoes nao esta expirado e url para avaliacao no jed ta definido
    else if(!$this->license_has_errors && !$this->updates_near_to_expire && (!empty($this->licenseInfoData->jed_url))) { ?>
        <?php // Exibe mensagem convidando a avaliar no JED ?>
        <div class='feedback-notice feedback-notice--success'>
            <span class="feedback-notice__icon fa fa-comment"></span>
            <div class="feedback-notice__content">
                <h4 class="feedback-notice__title">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_JED_EVALUATION_TITLE'); ?>
                </h4>
                <p class="feedback-notice__message">
                    <?php 
                    echo JText::sprintf("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_JED_EVALUATION", $this->licenseInfoData->jed_url);
                    ?>
                </p>
            </div>
        </div>
        
        <?php /* opcao que era utilizada para exibir que esta tudo ok com a extensao
        <div class='feedback-notice feedback-notice--success'>
            <span class="feedback-notice__icon fa fa-check-circle"></span>
            <div class="feedback-notice__content">
                <h4 class="feedback-notice__title">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_STATUS_TITLE'); ?>
                </h4>
                <p class="feedback-notice__message">
                    <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_NOTIFICATION_SUCCESS_TEXT'); ?>
                </p>
            </div>
        </div>
        <?php */ ?>
   <?php }
}

    /*
     * Inicio das informacoes gerais da extensao
     */
?>
    <div class="license-table">
        <h3 class="license-table__title">
            <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_INTRO_TITLE'); ?> 
        </h3>

        <div class="license-infos">
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_RESPONSIBLE_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php echo $this->licenseInfoData->responsible_name; ?>
                </div>
            </div>
            <?php
            // TODO: comentada exibicao do nome do plano (queremos tirar o vinculo do nome pq mudamos com frequencia o nome)
            /*
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_CONTRACTED_PLAN_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php echo $this->licenseInfoData->plan_title; ?>
                </div>
            </div>
            */
            ?>
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_SUPPORT_UPDATES_EXPIRATION_DATE_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php 
                        // Obtem configuracoes globais
                        $config     = JFactory::getConfig();
                        // Obtem offset das configuracoes globais
                        $dateOffSet = $config->get('offset', 'America/Sao_Paulo');
                        // Obtem objeto de data e hora atual
                        $dateLicenseObj = JFactory::getDate($this->licenseInfoData->support_updates_expiration, $dateOffSet);
                        // Converte a data para o formato definido para o idioma do usuario
                        echo $dateLicenseObj->format(JText::_("NOBOSS_EXTENSIONS_GLOBAL_DATE_FORMAT"));
                    ?>
                </div>
            </div>
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_SUPPORT_TECHNICAL_EXPIRATION_DATE_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php 
                        // Licenca esta com suporte tecnico ativo
                        if($this->licenseInfoData->inside_support_technical_expiration){
                            // Obtem configuracoes globais
                            $config     = JFactory::getConfig();
                            // Obtem offset das configuracoes globais
                            $dateOffSet = $config->get('offset', 'America/Sao_Paulo');
                            // Obtem objeto de data e hora atual
                            $dateLicenseObj = JFactory::getDate($this->licenseInfoData->support_technical_expiration, $dateOffSet);
                            // Converte a data para o formato definido para o idioma do usuario
                            echo $dateLicenseObj->format(JText::_("NOBOSS_EXTENSIONS_GLOBAL_DATE_FORMAT"));
                            // Exibe link para solicitar contato
                            ?>
                            <a target="_blank" href="<?php echo JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'); ?>">
                                <?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_SUPPORT_TECHNICAL_CONTACT_BUTTON'); ?>
                            </a>
                            <?php
                        }
                        // Licenca esta com suporte ativo expirado
                        else{
                            // Exibe mensagem que esta sem suporte com link para entrar em contato para regularizar
                            echo JText::sprintf("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_SUPPORT_TECHNICAL_INVALID_PERIOD", JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'));
                        }
                    ?>
                </div>
            </div>
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_LICENSE_NUMBER_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php echo $this->licenseInfoData->id_license; ?>
                </div>
            </div>
            <div class="license-infos__item">
                <div class="license-infos__label">
                    <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_EXTENSION_VERSION_LABEL"); ?>
                </div>
                <div class="license-infos__text">
                    <?php echo $this->licenseInfoData->extension_version; ?>
                </div>
            </div>
        </div>
        <?php // copyright ?> 
        <div class="nb-license-copyright">
            <?php echo JText::_("LIB_NOBOSS_FIELD_NOBOSSLICENSE_CONTENT_TAB_INFO_COPYRIGHT_VALUE"); ?>
        </div>
    </div>
</section>
