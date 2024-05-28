<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'cti_test' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'd aH_XLTn#X=g$vY{G3BQM_fK30G2v$L >|>`wBd<^{1;T(`ozvChOv!=O_=@[{>');
define('SECURE_AUTH_KEY',  '{OxyX{g$-UGa@C)qG-a)0 pszJL|u)Tzheue+`D4R=,QrzE^$Hd3M~2yT?pG. Tz');
define('LOGGED_IN_KEY',    'T!||+bfWJK}/]iwUGc#dM`/y<?mo Y&wt@!=A6*<}~+(GK$7|0Ilj]BrVO(,Sh[s');
define('NONCE_KEY',        'frF,b#_F[|V`1-mJv2S.H+Y@}BPVZ%u*+Z|/@p1qKCb1e[V+Ap?~qwNgFZ96aY0?');
define('AUTH_SALT',        '-J|~`gRW)o.+ItbwB<SzT%PnM-4I%Yejx1*cc=)|a,Ds_Zs8BfT nb_z>7{%*-p7');
define('SECURE_AUTH_SALT', 'CS)m2iDxw%M]#Grq3eUNl-/Je?|*PS|i,pa2@+inEL)hlmS^-gw;XS+<m04ZJ*[S');
define('LOGGED_IN_SALT',   'yd,-ZP|2N#]2`={xKX~)TPF?YV];V}@A9k*-Z7nci`NO|TnQlH:X7=uYF$Tl#VMH');
define('NONCE_SALT',       '=vHd<|+dN 9,KOQ6qeqQqI9ay:40lS[x|<#ap?[]D^l?})t!4f(M_VevAtkP.GQp');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
