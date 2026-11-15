<?php
/**
 * Plugin Name: Al Ghazali Institut — envoi des courriels
 * Description: Envoie les courriels de WordPress par le serveur SMTP déclaré dans l'environnement (SMTP_*). Sans SMTP_HOST, ne change rien et journalise les échecs.
 * Version: 1.0.0
 * Author: CNX Solutions
 *
 * L'image n'a pas de sendmail : sans SMTP, wp_mail() échoue. Le site n'a aucun
 * formulaire (inscriptions sur la plateforme externe) : seuls les courriels de
 * l'administration sont concernés (mot de passe oublié, alertes de WordPress).
 *
 * Les réglages vivent dans le .env de l'hôte, pas en base ni dans une
 * extension : le mot de passe SMTP ne passe ni par l'administration ni par un
 * export de la base.
 *
 *   SMTP_HOST       smtp-relay.brevo.com, smtp.gmail.com…  (vide : rien ne change)
 *   SMTP_PORT       587 par défaut
 *   SMTP_SECURE     tls (STARTTLS, défaut), ssl (port 465) ou none
 *   SMTP_USER       identifiant ; vide : pas d'authentification
 *   SMTP_PASSWORD
 *   SMTP_FROM       adresse d'expédition, autorisée par le SPF du domaine
 *   SMTP_FROM_NAME  nom affiché ; défaut : le nom du site
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bascule PHPMailer sur SMTP quand SMTP_HOST est défini.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Instance utilisée par wp_mail().
 */
function ag_smtp_configurer( $phpmailer ) {
	$hote = getenv( 'SMTP_HOST' );

	if ( ! $hote ) {
		return;
	}

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- propriétés de PHPMailer
	$phpmailer->isSMTP();
	$phpmailer->Host    = $hote;
	$phpmailer->Port    = getenv( 'SMTP_PORT' ) ? (int) getenv( 'SMTP_PORT' ) : 587;
	$phpmailer->Timeout = 15;

	$securite = getenv( 'SMTP_SECURE' ) ? getenv( 'SMTP_SECURE' ) : 'tls';

	if ( 'none' === $securite ) {
		$phpmailer->SMTPSecure  = '';
		$phpmailer->SMTPAutoTLS = false;
	} else {
		$phpmailer->SMTPSecure = 'ssl' === $securite ? 'ssl' : 'tls';
	}

	$utilisateur = getenv( 'SMTP_USER' );

	if ( $utilisateur ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $utilisateur;
		$phpmailer->Password = (string) getenv( 'SMTP_PASSWORD' );
	}
	// phpcs:enable
}
add_action( 'phpmailer_init', 'ag_smtp_configurer' );

/**
 * Adresse d'expédition : celle que le SMTP a le droit d'utiliser.
 *
 * Par défaut, WordPress expédie depuis wordpress@<domaine>, que le SPF du
 * domaine n'autorise pas : le message finirait en indésirable, ou refusé.
 *
 * @param string $adresse Adresse proposée par WordPress.
 * @return string
 */
function ag_smtp_expediteur( $adresse ) {
	return getenv( 'SMTP_FROM' ) ? getenv( 'SMTP_FROM' ) : $adresse;
}
add_filter( 'wp_mail_from', 'ag_smtp_expediteur' );

/**
 * Nom affiché de l'expéditeur.
 *
 * @param string $nom Nom proposé par WordPress.
 * @return string
 */
function ag_smtp_nom( $nom ) {
	if ( getenv( 'SMTP_FROM_NAME' ) ) {
		return getenv( 'SMTP_FROM_NAME' );
	}

	return getenv( 'SMTP_FROM' ) ? get_bloginfo( 'name' ) : $nom;
}
add_filter( 'wp_mail_from_name', 'ag_smtp_nom' );

/**
 * Journalise chaque échec d'envoi.
 *
 * En cas d'échec, wp_mail() renvoie false sans rien dire à personne. Écrit
 * dans le journal du conteneur (`docker logs alghazali-site-fpm`), l'échec devient
 * au moins visible.
 *
 * @param WP_Error $erreur Erreur transmise par wp_mail().
 */
function ag_smtp_journaliser( $erreur ) {
	$donnees = $erreur->get_error_data();
	$a       = isset( $donnees['to'] ) ? implode( ', ', (array) $donnees['to'] ) : '?';

	error_log( sprintf( '[courriel] échec vers %s : %s', $a, $erreur->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
}
add_action( 'wp_mail_failed', 'ag_smtp_journaliser' );
