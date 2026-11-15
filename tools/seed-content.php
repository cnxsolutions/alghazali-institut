<?php
/**
 * Contenu de départ du site : textes originaux optimisés pour le référencement, consacrés à la langue arabe.
 *
 * Horaires, rythmes et niveaux des formations sont des EXEMPLES à valider par l'institut avant
 * la mise en ligne. Aucun tarif ni aucune réduction n'est inventé : ces zones restent vides.
 * Les membres de l'équipe et les événements sont créés en brouillon.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Core\Content\Taxonomies;
use AlGhazzali\Seo\SeoFields;

/**
 * Créneaux hebdomadaires : [jour (1 = lundi), début, fin, niveau, classe, enseignant (slug), mode].
 *
 * @param list<array{0: int, 1: string, 2: string, 3: string, 4: string, 5: string}> $rows Créneaux.
 * @return list<array<string, string>>
 */
$ag_slots = static fn( array $rows ): array => array_map(
	static fn( array $row ): array => array(
		'day'     => (string) $row[0],
		'start'   => $row[1],
		'end'     => $row[2],
		'level'   => $row[3],
		'public'  => $row[4],
		'teacher' => $row[5],
		'mode'    => 'online',
	),
	$rows
);

$faq_home = array(
	'Faut-il savoir lire l’arabe pour commencer ?'               => 'Non. Le niveau 1 commence par l’alphabet, les voyelles et la lecture. Aucun prérequis n’est demandé : il suffit d’un peu de motivation et de régularité.',
	'Comment se déroulent les cours d’arabe en ligne ?'          => 'Les cours ont lieu en direct, en classe virtuelle, à heure fixe, avec votre enseignant et un petit groupe d’élèves de même niveau. Vous lisez, répondez et êtes corrigé pendant la séance, comme en classe.',
	'Les classes sont-elles séparées entre hommes et femmes ?'   => 'Oui. Chaque niveau ouvre des groupes réservés aux hommes et des groupes réservés aux femmes, à des horaires différents, indiqués dans l’emploi du temps.',
	'Combien de temps faut-il pour savoir lire l’arabe ?'        => 'Avec deux séances par semaine et un peu de révision, la plupart des débutants lisent l’arabe vocalisé en quelques semaines. La compréhension se construit ensuite, niveau après niveau.',
	'Comment connaître mon niveau ?'                              => 'Un court échange avant l’inscription permet d’évaluer votre lecture, votre vocabulaire et votre grammaire, puis de vous orienter vers le groupe adapté.',
	'Comment s’inscrire ?'                                        => 'Choisissez votre formation et votre créneau, puis contactez-nous : nous confirmons votre niveau et vous indiquons les modalités d’inscription.',
);

return array(

	'options'       => array(
		'blogname'               => 'Al Ghazali Institut',
		'blogdescription'        => 'Institut de langue arabe : cours d’arabe en ligne par niveaux, classes hommes et femmes séparées.',
		'WPLANG'                 => 'fr_FR',
		'timezone_string'        => 'Europe/Paris',
		'date_format'            => 'j F Y',
		'time_format'            => 'H:i',
		'start_of_week'          => 1,
		'permalink_structure'    => '/%postname%/',
		'default_comment_status' => 'closed',
		'default_ping_status'    => 'closed',
		'posts_per_page'         => 9,
		'blog_public'            => 1,
	),

	// Contenus des versions précédentes (Coran, sciences islamiques, jeunesse) : retirés.
	'obsolete'      => array(
		PostTypes::COURSE  => array( 'langue-arabe-adultes', 'coran-tajwid-adultes', 'sciences-islamiques-adultes', 'langue-arabe-enfants', 'coran-enfants', 'education-islamique-enfants' ),
		PostTypes::TEACHER => array( 'professeur-langue-arabe', 'professeure-coran-tajwid', 'professeur-sciences-islamiques' ),
		PostTypes::EVENT   => array( 'conference-heritage-al-ghazali' ),
		'page'             => array( 'cours-sciences-islamiques-en-ligne' ),
		'post'             => array( 'commencer-sciences-islamiques' ),
	),
	'obsolete_meta' => array( '_ag_level', '_ag_age', '_ag_lead', '_ag_landing_discipline' ),
	'obsolete_tax'  => array( 'ag_discipline', 'ag_audience' ),
	'obsolete_cats' => array( 'sciences-islamiques' ),

	'terms'         => array(
		Taxonomies::TEAM_GROUP => array(
			'direction'    => array( 'Direction', 'Direction et coordination pédagogique de l’institut.' ),
			'enseignants'  => array( 'Enseignants', 'Les enseignants des classes hommes.' ),
			'enseignantes' => array( 'Enseignantes', 'Les enseignantes des classes femmes.' ),
		),
		'category'             => array(
			'apprendre-arabe' => array( 'Apprendre l’arabe', 'Méthodes et conseils pour apprendre l’arabe facilement, niveau après niveau.' ),
		),
	),

	'pages'         => array(
		'accueil'                    => array(
			'title'   => 'Accueil',
			'excerpt' => '',
			'meta'    => array(
				SeoFields::TITLE       => 'Institut arabe : cours d’arabe en ligne | Al Ghazali',
				SeoFields::DESCRIPTION => 'Institut arabe en ligne : cours d’arabe par niveaux, en direct et en petits groupes, classes hommes et femmes séparées. Apprenez l’arabe facilement.',
			),
			'content' => ag_b_h2( 'Un institut arabe en ligne pour apprendre l’arabe pas à pas' )
				. ag_b_p( 'Al Ghazali Institut est un <strong>institut de langue arabe</strong> qui accompagne les francophones dans l’apprentissage de l’arabe, de la première lettre jusqu’à la lecture de textes authentiques. Nos <strong>cours d’arabe en ligne</strong> se déroulent en direct, en petits groupes, avec des enseignants qui suivent chaque élève.' )
				. ag_b_p( 'Le parcours est découpé en niveaux : lecture et écriture, vocabulaire, grammaire, compréhension, puis expression écrite et orale. Chaque niveau propose des classes séparées pour les hommes et pour les femmes, à des horaires différents.' )
				. ag_b_h2( 'Pourquoi choisir notre institut ?' )
				. ag_b_list(
					array(
						'<strong>Des cours en direct</strong>, pas des vidéos enregistrées : vous pratiquez à chaque séance.',
						'<strong>Une progression claire</strong>, découpée en niveaux avec des objectifs concrets.',
						'<strong>Des supports pensés pour les francophones</strong>, du premier pas jusqu’au niveau avancé.',
						'<strong>Des classes hommes et femmes séparées</strong>, à chaque niveau.',
					)
				)
				. ag_b_p( 'Découvrez nos <a href="/cours-arabe-en-ligne/">cours d’arabe en ligne</a>, notre méthode pour <a href="/apprendre-arabe-facilement/">apprendre l’arabe facilement</a>, puis trouvez votre créneau dans l’<a href="/emploi-du-temps/">emploi du temps</a>.' )
				. ag_b_h2( 'Questions fréquentes' )
				. ag_b_faq( $faq_home ),
		),

		'institut'                   => array(
			'title'   => 'L’institut Al Ghazali',
			'order'   => 1,
			'excerpt' => 'Un institut de langue arabe qui enseigne l’arabe aux francophones avec rigueur, clarté et bienveillance, par niveaux et en classes séparées pour hommes et femmes.',
			'meta'    => array(
				SeoFields::TITLE       => 'Institut arabe Al Ghazali : notre mission et nos valeurs',
				SeoFields::DESCRIPTION => 'Découvrez l’institut arabe Al Ghazali : sa mission, ses valeurs et sa méthode pour enseigner la langue arabe aux francophones, par niveaux et en direct.',
			),
			'content' => ag_b_h2( 'Notre mission' )
				. ag_b_p( 'Rendre la langue arabe accessible à chaque francophone. Nous sommes convaincus qu’on peut apprendre l’arabe à tout âge, à condition d’être bien guidé : une méthode claire, des objectifs précis à chaque niveau et des enseignants disponibles.' )
				. ag_b_h2( 'Pourquoi « Al Ghazali » ?' )
				. ag_b_p( 'Notre institut porte le nom d’Abû Hâmid al-Ghazâlî (1058-1111), qui enseigna à la Nizâmiyya de Bagdad, l’une des grandes écoles de son époque. Nous retenons de lui une exigence de pédagogue : expliquer avec clarté, avancer avec méthode et faire de chaque leçon un vrai progrès.' )
				. ag_b_h2( 'Nos valeurs' )
				. ag_b_list(
					array(
						'<strong>La rigueur</strong> : un enseignement structuré, niveau après niveau.',
						'<strong>La clarté</strong> : des explications simples, en français, pour progresser sans se décourager.',
						'<strong>La bienveillance</strong> : un cadre respectueux où chacun peut poser ses questions.',
						'<strong>La constance</strong> : de petits pas réguliers plutôt que de grands efforts sans lendemain.',
					)
				)
				. ag_b_h2( 'Des classes pour les hommes et pour les femmes' )
				. ag_b_p( 'Chaque niveau ouvre des groupes séparés, encadrés par nos enseignants pour les classes hommes et par nos enseignantes pour les classes femmes. Consultez l’<a href="/emploi-du-temps/">emploi du temps</a> pour trouver votre créneau.' )
				. ag_b_h2( 'Notre équipe' )
				. ag_b_p( 'La direction pédagogique et les enseignants de l’institut sont présentés sur la page <a href="/equipe/">Notre équipe</a>.' ),
		),

		'cours-arabe-en-ligne'       => array(
			'title'    => 'Cours d’arabe en ligne',
			'order'    => 10,
			'template' => 'page-templates/landing.php',
			'excerpt'  => 'Des cours d’arabe en ligne en direct, en petits groupes, avec des classes séparées pour hommes et femmes : de l’alphabet à la compréhension des textes.',
			'meta'     => array(
				SeoFields::TITLE       => 'Cours d’arabe en ligne en direct | Al Ghazali Institut',
				SeoFields::DESCRIPTION => 'Cours d’arabe en ligne en direct : petits groupes, parcours par niveaux, classes hommes et femmes séparées. Lisez, écrivez et comprenez l’arabe pas à pas.',
			),
			'content'  => ag_b_h2( 'Apprendre l’arabe en ligne, avec un vrai enseignant' )
				. ag_b_p( 'Nos <strong>cours d’arabe en ligne</strong> ne sont pas une suite de vidéos : ce sont de vraies classes, en direct, où votre enseignant vous fait lire, vous corrige et répond à vos questions. Vous profitez de la qualité d’un enseignement en classe, sans les contraintes de déplacement.' )
				. ag_b_p( 'Nous enseignons l’arabe littéraire (<em>fusha</em>), la langue de l’écrit, de la littérature et des médias : la base commune à tout le monde arabophone.' )
				. ag_b_h2( 'Un parcours en niveaux' )
				. ag_b_h3( 'Débutant : lire et écrire' )
				. ag_b_p( 'Les 28 lettres de l’alphabet, leurs formes selon leur position dans le mot, les voyelles courtes et longues, puis la lecture fluide de textes vocalisés.' )
				. ag_b_h3( 'Intermédiaire : comprendre et construire' )
				. ag_b_p( 'Le vocabulaire du quotidien, les bases de la grammaire (<em>nahw</em>) et de la morphologie (<em>sarf</em>), les premiers textes authentiques.' )
				. ag_b_h3( 'Avancé : lire et s’exprimer' )
				. ag_b_p( 'L’analyse grammaticale, la lecture de textes littéraires et de presse, l’expression écrite et orale.' )
				. ag_b_h2( 'Des classes hommes et femmes séparées' )
				. ag_b_p( 'Chaque niveau ouvre des groupes réservés aux hommes et des groupes réservés aux femmes, à des horaires différents. Vous choisissez le créneau qui vous convient dans l’<a href="/emploi-du-temps/">emploi du temps</a>.' )
				. ag_b_h2( 'Comment se déroule un cours ?' )
				. ag_b_list(
					array(
						'Vous rejoignez la classe virtuelle à l’heure prévue, depuis un ordinateur, une tablette ou un téléphone.',
						'L’enseignant présente la leçon du jour et la met en pratique avec le groupe.',
						'Chacun lit, répond et participe : la correction se fait en direct.',
						'Vous repartez avec un support de cours et des exercices pour réviser.',
					),
					true
				)
				. ag_b_h2( 'Pour qui sont nos cours d’arabe en ligne ?' )
				. ag_b_list(
					array(
						'Les grands débutants qui veulent lire l’arabe pour la première fois.',
						'Ceux qui savent déchiffrer mais veulent enfin comprendre ce qu’ils lisent.',
						'Ceux qui veulent parler et échanger en arabe avec aisance.',
						'Les francophones éloignés d’un institut, en France comme à l’étranger.',
					)
				)
				. ag_b_callout( ag_b_p( '<strong>Bon à savoir :</strong> avant de commencer, nous échangeons avec vous pour évaluer votre niveau et vous orienter vers le bon groupe. Découvrez aussi <a href="/apprendre-arabe-facilement/">notre méthode pour apprendre l’arabe facilement</a>.' ) )
				. ag_b_h2( 'Questions fréquentes sur nos cours d’arabe en ligne' )
				. ag_b_faq(
					array(
						'De quel matériel ai-je besoin ?'                         => 'Un ordinateur, une tablette ou un smartphone avec une connexion internet stable, des écouteurs et de quoi prendre des notes. Le lien de la classe vous est communiqué avant le premier cours.',
						'Les cours en ligne sont-ils aussi efficaces qu’en classe ?' => 'Oui, lorsqu’ils sont en direct et en petit groupe. Vous êtes sollicité à chaque séance, corrigé en temps réel et suivi par le même enseignant tout au long de l’année.',
						'Quel arabe enseignez-vous ?'                             => 'L’arabe littéraire (fusha), celui de l’écrit, de la littérature et des médias. C’est la base commune à tout le monde arabophone.',
						'Les classes sont-elles mixtes ?'                         => 'Non : chaque niveau propose des classes pour les hommes et des classes pour les femmes, à des horaires différents.',
						'Puis-je suivre les cours depuis l’étranger ?'           => 'Oui. Les cours étant en ligne, vous pouvez les suivre depuis n’importe quel pays ; les horaires sont indiqués à l’heure de Paris.',
					)
				),
		),

		'apprendre-arabe-facilement' => array(
			'title'    => 'Apprendre l’arabe facilement : notre méthode',
			'order'    => 11,
			'template' => 'page-templates/landing.php',
			'excerpt'  => 'Apprendre l’arabe facilement, c’est possible : une méthode progressive, des objectifs clairs et un enseignant pour vous guider, du premier mot à la lecture de textes.',
			'meta'     => array(
				SeoFields::TITLE       => 'Apprendre l’arabe facilement : la méthode pas à pas',
				SeoFields::DESCRIPTION => 'Comment apprendre l’arabe facilement quand on est francophone ? Notre méthode en 4 étapes, 7 conseils concrets et des cours en ligne pour bien débuter.',
			),
			'content'  => ag_b_h2( 'L’arabe est-il difficile à apprendre ?' )
				. ag_b_p( 'L’arabe a la réputation d’être une langue difficile : un nouvel alphabet, une écriture de droite à gauche, des sons qui n’existent pas en français. Ces obstacles sont pourtant surtout ceux du début. L’alphabet s’apprend en quelques semaines, et la langue repose sur une logique très régulière : la plupart des mots dérivent d’une <strong>racine de trois lettres</strong>.' )
				. ag_b_p( 'Ainsi, la racine ' . ag_b_ar( 'ك ت ب' ) . ' (k-t-b), liée à l’idée d’écrire, donne ' . ag_b_ar( 'كَتَبَ' ) . ' (il a écrit), ' . ag_b_ar( 'كِتَاب' ) . ' (un livre) ou ' . ag_b_ar( 'مَكْتَبَة' ) . ' (une bibliothèque). Une fois ce mécanisme compris, chaque nouveau mot se retient plus facilement.' )
				. ag_b_h2( 'Notre méthode en 4 étapes' )
				. ag_b_h3( '1. Maîtriser l’alphabet et la lecture' )
				. ag_b_p( 'Les lettres, les voyelles et les règles de lecture, travaillées jusqu’à lire un texte vocalisé sans hésiter. C’est le socle de tout le reste.' )
				. ag_b_h3( '2. Construire un vocabulaire utile' )
				. ag_b_p( 'Des mots choisis pour leur fréquence, appris par racines et par thèmes, et réutilisés de séance en séance pour qu’ils deviennent naturels.' )
				. ag_b_h3( '3. Comprendre la grammaire par l’usage' )
				. ag_b_p( 'Chaque règle est d’abord observée dans des phrases simples, puis expliquée et enfin pratiquée. La grammaire devient un outil, pas une liste à apprendre par cœur.' )
				. ag_b_h3( '4. Lire, écouter et s’exprimer' )
				. ag_b_p( 'Des textes de plus en plus riches, de l’écoute régulière et des échanges en classe pour gagner en compréhension et en aisance.' )
				. ag_b_h2( '7 conseils pour apprendre l’arabe plus facilement' )
				. ag_b_list(
					array(
						'Étudiez un peu chaque jour : vingt minutes quotidiennes valent mieux que trois heures le dimanche.',
						'Lisez à voix haute pour fixer la prononciation et gagner en fluidité.',
						'Apprenez les mots par racines plutôt qu’isolément.',
						'Révisez avec des cartes mémoire et la répétition espacée.',
						'Écoutez de l’arabe régulièrement : lectures à voix haute, actualités, podcasts.',
						'Fixez-vous des objectifs précis et mesurables à chaque niveau.',
						'Faites-vous accompagner : un enseignant corrige vos erreurs avant qu’elles ne deviennent des habitudes.',
					),
					true
				)
				. ag_b_h2( 'Débuter avec un enseignant' )
				. ag_b_p( 'Seul, on se décourage vite. Dans nos <a href="/cours-arabe-en-ligne/">cours d’arabe en ligne</a>, vous avancez avec un groupe de votre niveau et un enseignant qui adapte le rythme. C’est la façon la plus simple, et la plus sûre, d’apprendre l’arabe facilement.' )
				. ag_b_h2( 'Questions fréquentes' )
				. ag_b_faq(
					array(
						'Peut-on apprendre l’arabe seul ?'                    => 'On peut commencer seul avec de bons supports, mais la prononciation et la grammaire se corrigent difficilement sans retour extérieur. Un enseignant vous fait gagner un temps précieux et évite les erreurs durables.',
						'Combien de temps pour apprendre l’alphabet arabe ?' => 'En général, quatre à huit semaines suffisent pour lire l’arabe vocalisé, avec deux séances par semaine et un peu de pratique quotidienne.',
						'Arabe littéraire ou dialecte : par quoi commencer ?' => 'Pour lire, comprendre les textes et être compris dans tout le monde arabe, l’arabe littéraire est la base la plus utile. Les dialectes s’apprennent ensuite plus facilement.',
						'Est-il trop tard pour apprendre l’arabe à l’âge adulte ?' => 'Non. Les adultes progressent vite grâce à la compréhension des règles et à la régularité : l’essentiel est d’avancer avec méthode, niveau après niveau.',
					)
				),
		),

		'emploi-du-temps'            => array(
			'title'    => 'Emploi du temps',
			'order'    => 3,
			'template' => 'page-templates/schedule.php',
			'excerpt'  => 'Les horaires de toutes nos classes d’arabe, jour par jour. Filtrez par formation et par classe (hommes ou femmes) pour trouver votre créneau.',
			'meta'     => array(
				SeoFields::TITLE       => 'Emploi du temps des cours d’arabe | Al Ghazali Institut',
				SeoFields::DESCRIPTION => 'Consultez l’emploi du temps de nos cours d’arabe en ligne, jour par jour : niveaux, horaires et classes séparées pour hommes et femmes.',
			),
			'content'  => ag_b_p( 'Les horaires sont indiqués à l’heure de Paris. Chaque fiche formation détaille le programme de ses niveaux.' ),
		),

		'contact'                    => array(
			'title'    => 'Contact',
			'order'    => 5,
			'template' => 'page-templates/contact.php',
			'excerpt'  => 'Une question sur nos formations, votre niveau ou l’organisation des cours ? Écrivez-nous : nous vous répondons et vous orientons vers le groupe adapté.',
			'meta'     => array(
				SeoFields::TITLE       => 'Contact : Al Ghazali Institut',
				SeoFields::DESCRIPTION => 'Contactez Al Ghazali Institut pour toute question sur nos cours d’arabe en ligne : niveaux, horaires, classes hommes et femmes. Nous vous orientons.',
			),
			'content'  => ag_b_h2( 'Nous vous orientons' )
				. ag_b_p( 'Indiquez-nous votre niveau actuel, votre objectif (lire, comprendre, parler) et vos disponibilités, en précisant si vous souhaitez rejoindre une classe hommes ou une classe femmes : nous vous proposerons le groupe le plus adapté.' ),
		),

		'blog'                       => array(
			'title'   => 'Blog',
			'order'   => 4,
			'excerpt' => 'Conseils et méthodes pour apprendre l’arabe facilement, progresser niveau après niveau et bien choisir ses cours.',
			'meta'    => array(
				SeoFields::TITLE       => 'Blog : conseils pour apprendre l’arabe | Al Ghazali',
				SeoFields::DESCRIPTION => 'Nos conseils pour apprendre l’arabe facilement : alphabet, méthode, choix des cours et progression par niveaux.',
			),
			'content' => '',
		),

		'mentions-legales'           => array(
			'title'   => 'Mentions légales',
			'order'   => 90,
			'excerpt' => '',
			'meta'    => array( SeoFields::NOINDEX => '1' ),
			'content' => ag_b_h2( 'Éditeur du site' )
				. ag_b_p( '[Raison sociale], [forme juridique], dont le siège est situé [adresse complète]. [Numéro RNA ou SIREN]. Directeur ou directrice de la publication : [nom]. Contact : [e-mail].' )
				. ag_b_h2( 'Hébergement' )
				. ag_b_p( '[Nom de l’hébergeur], [adresse], [téléphone].' )
				. ag_b_h2( 'Conception et développement' )
				. ag_b_p( 'Site conçu et développé par <a href="https://cnx-solutions.fr">CNX Solutions</a>.' )
				. ag_b_h2( 'Propriété intellectuelle' )
				. ag_b_p( 'L’ensemble des contenus de ce site (textes, visuels, logo) est protégé. Toute reproduction sans autorisation préalable est interdite.' )
				. ag_b_h2( 'Données personnelles' )
				. ag_b_p( 'Voir notre <a href="/politique-de-confidentialite/">politique de confidentialité</a>.' ),
		),

		'politique-de-confidentialite' => array(
			'title'   => 'Politique de confidentialité',
			'order'   => 91,
			'excerpt' => '',
			'meta'    => array( SeoFields::NOINDEX => '1' ),
			'content' => ag_b_h2( 'Responsable du traitement' )
				. ag_b_p( '[Raison sociale], [adresse], [e-mail].' )
				. ag_b_h2( 'Données collectées' )
				. ag_b_p( 'Ce site ne comporte aucun formulaire. Lorsque vous nous contactez par e-mail, par téléphone ou par WhatsApp, les informations transmises servent uniquement à vous répondre et à vous orienter.' )
				. ag_b_h2( 'Cookies et mesure d’audience' )
				. ag_b_p( 'Le site ne dépose aucun cookie publicitaire. [Si un outil de mesure d’audience est ajouté, le décrire ici et recueillir le consentement lorsque la loi l’exige.]' )
				. ag_b_h2( 'Durée de conservation' )
				. ag_b_p( 'Les échanges sont conservés le temps nécessaire au traitement de votre demande, puis supprimés au plus tard [durée] après le dernier contact.' )
				. ag_b_h2( 'Plateforme d’inscription' )
				. ag_b_p( '[À compléter lors de la mise en service de la plateforme d’inscription et de l’espace élève, qui disposent de leur propre politique de confidentialité.]' )
				. ag_b_h2( 'Vos droits' )
				. ag_b_p( 'Conformément au RGPD, vous disposez d’un droit d’accès, de rectification, d’effacement, d’opposition et de limitation du traitement de vos données. Pour les exercer : [e-mail]. Vous pouvez aussi adresser une réclamation à la CNIL (www.cnil.fr).' ),
		),
	),

	// Brouillons : à compléter avec de vrais profils (photo, présentation) avant publication.
	'teachers'      => array(
		'direction-pedagogique' => array(
			'title'   => 'Direction pédagogique',
			'order'   => 1,
			'group'   => 'direction',
			'excerpt' => 'Présentation à compléter : parcours, diplômes et rôle au sein de l’institut.',
			'meta'    => array( Fields::ROLE => 'Responsable pédagogique' ),
		),
		'enseignant-arabe'      => array(
			'title'   => 'Enseignant d’arabe',
			'order'   => 2,
			'group'   => 'enseignants',
			'excerpt' => 'Présentation à compléter : diplômes, spécialité et années d’enseignement.',
			'meta'    => array( Fields::ROLE => 'Enseignant d’arabe · classes hommes' ),
		),
		'enseignante-arabe'     => array(
			'title'   => 'Enseignante d’arabe',
			'order'   => 3,
			'group'   => 'enseignantes',
			'excerpt' => 'Présentation à compléter : diplômes, spécialité et années d’enseignement.',
			'meta'    => array( Fields::ROLE => 'Enseignante d’arabe · classes femmes' ),
		),
	),

	// Les enseignants des créneaux sont indiqués par leur slug : seed.php les remplace par leur ID.
	'courses'       => array(
		'arabe-litteraire'     => array(
			'title'   => 'Cours d’arabe littéraire',
			'order'   => 1,
			'excerpt' => 'Lire, écrire, comprendre et s’exprimer en arabe littéraire : un parcours en cinq niveaux, du grand débutant à l’avancé, en direct et en petits groupes.',
			'meta'    => array(
				Fields::KICKER         => 'Langue arabe',
				Fields::ARABIC         => 'العربية الفصحى',
				Fields::FORMAT         => 'online',
				Fields::RHYTHM         => '2 séances de 1 h 30 par semaine',
				Fields::DURATION       => 'Année scolaire, de septembre à juin',
				Fields::LEVELS_INTRO   => 'Cinq niveaux pour aller de la première lettre à la lecture autonome de textes authentiques. Chaque niveau consolide des acquis solides avant d’ouvrir le suivant.',
				Fields::LEVELS         => array(
					array(
						'name'        => 'Niveau 1',
						'subtitle'    => 'Lire et écrire',
						'note'        => '',
						'description' => 'Le socle de tout le parcours : on découvre l’alphabet, les sons propres à l’arabe et les règles de lecture, jusqu’à lire un texte vocalisé avec assurance.',
						'objectives'  => "Lire et écrire les 28 lettres dans toutes leurs formes\nMaîtriser les voyelles courtes et longues, le soukoun, la chadda et le tanwîn\nLire un texte vocalisé de façon fluide",
						'programme'   => "L’alphabet et la forme des lettres selon leur position\nLes voyelles et les signes de lecture\nLa lecture syllabique, puis des mots et des phrases\nL’écriture et les liaisons entre les lettres\nUn premier vocabulaire du quotidien",
						'method'      => 'Une progression lettre par lettre : la lettre isolée, puis vocalisée, puis assemblée en syllabes et en mots. Chaque séance alterne lecture à voix haute, écriture et dictée, avec une correction immédiate de la prononciation.',
						'material'    => 'Livret de lecture et fiches d’écriture fournis',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 2',
						'subtitle'    => 'Premières phrases',
						'note'        => '',
						'description' => 'On passe de la lecture à la compréhension : construire ses premières phrases, se présenter et parler de son quotidien.',
						'objectives'  => "Construire des phrases nominales simples\nSe présenter et décrire son environnement\nComprendre un court texte sur un thème familier",
						'programme'   => "Les pronoms, les démonstratifs et les prépositions\nLe masculin et le féminin, le singulier et le pluriel\nLa phrase nominale et l’annexion (idâfa)\nLe vocabulaire de la famille, de la maison et du temps",
						'method'      => 'Chaque notion est d’abord rencontrée dans un dialogue ou un texte court, puis expliquée et pratiquée à l’oral comme à l’écrit.',
						'material'    => 'Manuel de niveau 2 fourni',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 3',
						'subtitle'    => 'Le verbe et la phrase',
						'note'        => '',
						'description' => 'Le cœur de la grammaire : la conjugaison et la phrase verbale, pour raconter, décrire et exprimer des actions.',
						'objectives'  => "Conjuguer les verbes au passé, au présent et à l’impératif\nComprendre le système des racines et des schèmes\nRédiger un paragraphe simple",
						'programme'   => "La conjugaison du verbe trilitère\nLa phrase verbale et l’accord du verbe\nLes racines et les schèmes les plus fréquents\nLes cas et les terminaisons : premières règles",
						'method'      => 'La grammaire s’apprend par l’usage : observation dans des phrases, règle formulée avec l’enseignant, puis exercices oraux et écrits corrigés en classe.',
						'material'    => 'Manuel de niveau 3 fourni',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 4',
						'subtitle'    => 'Comprendre les textes',
						'note'        => '',
						'description' => 'Lire des textes authentiques de plus en plus longs, enrichir son vocabulaire et affiner l’analyse grammaticale.',
						'objectives'  => "Lire et comprendre un texte authentique non vocalisé\nAnalyser la fonction des mots dans la phrase\nRésumer un texte à l’écrit et à l’oral",
						'programme'   => "Les formes verbales dérivées\nLes phrases complexes et les subordonnées\nL’analyse grammaticale (i‘râb)\nDes textes variés : récits, articles, extraits littéraires",
						'method'      => 'Chaque séance s’appuie sur un texte : lecture, explication du vocabulaire, analyse des structures, puis reformulation par les élèves.',
						'material'    => 'Recueil de textes fourni',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 5',
						'subtitle'    => 'Perfectionnement',
						'note'        => 'Ouverture selon les inscriptions',
						'description' => 'Gagner en finesse et en aisance : lire sans voyelles, saisir les nuances et s’exprimer avec précision.',
						'objectives'  => "Lire des textes longs sans vocalisation\nMaîtriser les structures avancées de la langue\nPrésenter un exposé et débattre en arabe",
						'programme'   => "Les structures avancées et le style\nLa lecture de textes littéraires et de presse\nL’expression écrite argumentée\nLa prise de parole : exposés et discussions",
						'method'      => 'Le travail se fait en grande partie en arabe : lectures préparées, échanges en classe et productions écrites corrigées individuellement.',
						'material'    => 'Textes choisis par l’enseignant',
						'price'       => '',
						'price_note'  => '',
					),
				),
				Fields::FEATURES       => array(
					array(
						'label' => 'Format',
						'title' => 'En ligne, en direct',
						'text'  => 'Des classes virtuelles à heure fixe : vous lisez, répondez et êtes corrigé pendant la séance.',
					),
					array(
						'label' => 'Rythme',
						'title' => 'Deux séances par semaine',
						'text'  => 'Une régularité qui fait progresser, avec des exercices courts entre deux séances.',
					),
					array(
						'label' => 'Test de niveau',
						'title' => 'Un échange de 15 minutes',
						'text'  => 'Un court entretien avec un enseignant pour évaluer votre niveau et vous orienter vers le bon groupe.',
					),
				),
				Fields::STEPS_TITLE    => 'Comment se déroule une séance ?',
				Fields::STEPS          => "Vous rejoignez la classe virtuelle à l’heure prévue, depuis un ordinateur, une tablette ou un téléphone.\nL’enseignant présente la leçon du jour à partir d’un texte ou d’un dialogue.\nChacun lit, répond et participe : la correction se fait en direct.\nLa séance se termine par des exercices à faire avant le prochain cours.",
				Fields::SLOTS          => $ag_slots(
					array(
						array( 1, '19:00', '20:30', 'Niveau 1', 'hommes', 'enseignant-arabe' ),
						array( 3, '19:00', '20:30', 'Niveau 1', 'hommes', 'enseignant-arabe' ),
						array( 2, '10:00', '11:30', 'Niveau 1', 'femmes', 'enseignante-arabe' ),
						array( 4, '10:00', '11:30', 'Niveau 1', 'femmes', 'enseignante-arabe' ),
						array( 2, '19:00', '20:30', 'Niveau 2', 'hommes', 'enseignant-arabe' ),
						array( 4, '19:00', '20:30', 'Niveau 2', 'hommes', 'enseignant-arabe' ),
						array( 1, '10:00', '11:30', 'Niveau 2', 'femmes', 'enseignante-arabe' ),
						array( 3, '10:00', '11:30', 'Niveau 2', 'femmes', 'enseignante-arabe' ),
						array( 6, '09:30', '11:00', 'Niveau 3', 'hommes', 'enseignant-arabe' ),
						array( 7, '09:30', '11:00', 'Niveau 3', 'hommes', 'enseignant-arabe' ),
						array( 6, '14:00', '15:30', 'Niveau 3', 'femmes', 'enseignante-arabe' ),
						array( 7, '14:00', '15:30', 'Niveau 3', 'femmes', 'enseignante-arabe' ),
						array( 1, '20:45', '22:15', 'Niveau 4', 'hommes', 'enseignant-arabe' ),
						array( 3, '20:45', '22:15', 'Niveau 4', 'hommes', 'enseignant-arabe' ),
						array( 2, '14:00', '15:30', 'Niveau 4', 'femmes', 'enseignante-arabe' ),
						array( 4, '14:00', '15:30', 'Niveau 4', 'femmes', 'enseignante-arabe' ),
					)
				),
				Fields::FAQ            => array(
					array(
						'question' => 'Je ne connais pas l’alphabet : puis-je m’inscrire ?',
						'answer'   => 'Oui, le niveau 1 commence par l’alphabet. Aucun prérequis n’est demandé.',
					),
					array(
						'question' => 'Comment savoir par quel niveau commencer ?',
						'answer'   => 'Un court échange avant l’inscription permet d’évaluer votre lecture, votre vocabulaire et votre grammaire, puis de vous orienter vers le groupe adapté.',
					),
					array(
						'question' => 'Que se passe-t-il si je manque une séance ?',
						'answer'   => 'Votre enseignant vous indique la leçon et les exercices à rattraper pour rester au niveau du groupe.',
					),
					array(
						'question' => 'Peut-on changer de créneau en cours d’année ?',
						'answer'   => 'Oui, dans la limite des places disponibles, vers un autre créneau du même niveau et de la même classe.',
					),
				),
				SeoFields::TITLE       => 'Cours d’arabe littéraire en ligne, 5 niveaux | Al Ghazali',
				SeoFields::DESCRIPTION => 'Cours d’arabe littéraire en ligne : 5 niveaux, de l’alphabet aux textes authentiques. En direct, en petits groupes, classes hommes et femmes séparées.',
			),
			'content' => ag_b_h2( 'Pourquoi apprendre l’arabe littéraire ?' )
				. ag_b_p( 'L’arabe littéraire (<em>fusha</em>) est la langue de l’écrit, de la littérature, de la presse et des échanges officiels dans tout le monde arabe. C’est la base commune qui permet ensuite de comprendre plus facilement les dialectes.' )
				. ag_b_p( 'Ce parcours s’adresse aux francophones qui veulent apprendre l’arabe de façon structurée, avec des objectifs clairs à chaque niveau et un enseignant qui suit leur progression. Pour travailler aussi l’oral, il se complète naturellement par notre cours d’<a href="/formations/arabe-oral-immersion/">arabe oral en immersion</a>.' ),
		),

		'arabe-oral-immersion' => array(
			'title'   => 'Arabe oral : conversation et immersion',
			'order'   => 2,
			'excerpt' => 'Parler et comprendre l’arabe grâce à des séances menées en arabe : dialogues, mises en situation et débats, en petits groupes et en direct.',
			'meta'    => array(
				Fields::KICKER         => 'Expression orale',
				Fields::ARABIC         => 'المحادثة',
				Fields::FORMAT         => 'online',
				Fields::RHYTHM         => '1 à 2 séances d’une heure par semaine',
				Fields::DURATION       => 'Année scolaire, de septembre à juin',
				Fields::LEVELS_INTRO   => 'Trois niveaux pour passer de la lecture à la conversation. Prérequis : savoir lire l’arabe vocalisé (niveau 1 du parcours d’arabe littéraire).',
				Fields::LEVELS         => array(
					array(
						'name'        => 'Niveau 1',
						'subtitle'    => 'Premiers échanges',
						'note'        => '',
						'description' => 'Prendre la parole dès les premières séances : se présenter, poser des questions et comprendre des réponses simples.',
						'objectives'  => "Se présenter et présenter quelqu’un\nPoser des questions simples et y répondre\nComprendre un dialogue court sur un sujet familier",
						'programme'   => "Les salutations et les présentations\nLes questions du quotidien : lieux, horaires, prix\nLes nombres et l’heure\nDes jeux de rôle en binôme",
						'method'      => 'La séance se déroule en arabe, avec des mots simples, des images et des gestes. Chaque élève prend la parole plusieurs fois par séance.',
						'material'    => 'Fiches de vocabulaire et enregistrements audio fournis',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 2',
						'subtitle'    => 'Conversation courante',
						'note'        => '',
						'description' => 'Tenir une conversation sur la vie quotidienne, raconter un événement et donner son avis simplement.',
						'objectives'  => "Raconter un événement passé\nDécrire une situation et exprimer une préférence\nComprendre l’essentiel d’un document audio simple",
						'programme'   => "Le récit au passé\nLa comparaison et l’expression de l’opinion\nLes situations pratiques : voyage, travail, achats\nL’écoute de documents audio courts",
						'method'      => 'Mises en situation concrètes, écoute de documents authentiques et reformulation : l’enseignant corrige sans interrompre, puis reprend les erreurs en fin d’activité.',
						'material'    => 'Fiches de vocabulaire et enregistrements audio fournis',
						'price'       => '',
						'price_note'  => '',
					),
					array(
						'name'        => 'Niveau 3',
						'subtitle'    => 'Aisance et débat',
						'note'        => '',
						'description' => 'S’exprimer avec aisance sur des sujets variés, argumenter et débattre.',
						'objectives'  => "Argumenter et défendre un point de vue\nComprendre un reportage ou une interview\nPrésenter un exposé de quelques minutes",
						'programme'   => "Des débats sur des thèmes de société\nDes exposés préparés et les questions du groupe\nL’écoute de reportages et d’interviews\nLes expressions et tournures idiomatiques",
						'method'      => 'Les élèves préparent de courtes interventions, puis échangent en groupe ; l’enseignant enrichit le vocabulaire et affine la précision.',
						'material'    => 'Documents audio et vidéo choisis par l’enseignant',
						'price'       => '',
						'price_note'  => '',
					),
				),
				Fields::FEATURES       => array(
					array(
						'label' => 'Cadre',
						'title' => 'Un cours mené en arabe',
						'text'  => 'L’enseignant s’exprime en arabe : vous apprenez à comprendre et à répondre sans passer par le français.',
					),
					array(
						'label' => 'Groupes',
						'title' => 'De petits groupes',
						'text'  => 'Des effectifs réduits pour que chacun prenne la parole à chaque séance.',
					),
					array(
						'label' => 'Prérequis',
						'title' => 'Savoir lire l’arabe',
						'text'  => 'Le niveau 1 du parcours d’arabe littéraire suffit pour commencer.',
					),
				),
				Fields::STEPS_TITLE    => 'Comment se passe une séance en immersion ?',
				Fields::STEPS          => "La séance se déroule en arabe, du début à la fin.\nL’enseignant introduit le thème du jour par un dialogue ou un document audio.\nLes élèves pratiquent en binôme, puis devant le groupe : questions, jeux de rôle, débats.\nL’enseignant reprend les erreurs et donne le vocabulaire utile pour la séance suivante.",
				Fields::SLOTS          => $ag_slots(
					array(
						array( 5, '19:00', '20:00', 'Niveau 1', 'hommes', 'enseignant-arabe' ),
						array( 7, '11:15', '12:15', 'Niveau 1', 'hommes', 'enseignant-arabe' ),
						array( 5, '10:00', '11:00', 'Niveau 1', 'femmes', 'enseignante-arabe' ),
						array( 7, '15:45', '16:45', 'Niveau 1', 'femmes', 'enseignante-arabe' ),
						array( 5, '20:15', '21:15', 'Niveau 2', 'hommes', 'enseignant-arabe' ),
						array( 5, '11:15', '12:15', 'Niveau 2', 'femmes', 'enseignante-arabe' ),
						array( 6, '11:15', '12:15', 'Niveau 3', 'hommes', 'enseignant-arabe' ),
						array( 6, '15:45', '16:45', 'Niveau 3', 'femmes', 'enseignante-arabe' ),
					)
				),
				Fields::FAQ            => array(
					array(
						'question' => 'Faut-il déjà parler arabe ?',
						'answer'   => 'Non. Il suffit de savoir lire l’arabe vocalisé : le niveau 1 commence par les échanges les plus simples.',
					),
					array(
						'question' => 'Quel arabe parle-t-on en cours ?',
						'answer'   => 'Un arabe standard, compris dans tout le monde arabe, proche de l’arabe littéraire étudié dans notre parcours écrit.',
					),
					array(
						'question' => 'Peut-on suivre ce cours en même temps que l’arabe littéraire ?',
						'answer'   => 'Oui, c’est même recommandé : l’oral renforce le vocabulaire et la grammaire vus dans le parcours écrit.',
					),
				),
				SeoFields::TITLE       => 'Cours d’arabe oral en ligne : conversation | Al Ghazali',
				SeoFields::DESCRIPTION => 'Parlez arabe avec nos cours de conversation en ligne : séances menées en arabe, 3 niveaux, petits groupes, classes hommes et femmes séparées.',
			),
			'content' => ag_b_h2( 'Apprendre à parler arabe' )
				. ag_b_p( 'Beaucoup d’apprenants savent lire et connaissent la grammaire, mais n’osent pas parler. Ce cours est pensé pour franchir ce cap : la séance se déroule en arabe et chaque élève prend la parole, dans un cadre bienveillant.' )
				. ag_b_p( 'Il complète le <a href="/formations/arabe-litteraire/">cours d’arabe littéraire</a> : l’écrit donne les structures, l’oral les fait vivre.' ),
		),
	),

	'posts'         => array(
		'apprendre-alphabet-arabe'     => array(
			'title'    => 'Apprendre l’alphabet arabe facilement : la méthode en 5 étapes',
			'category' => 'apprendre-arabe',
			'excerpt'  => 'Vingt-huit lettres, une écriture de droite à gauche et des voyelles qui ne s’écrivent pas toujours : voici une méthode simple en 5 étapes pour lire l’arabe rapidement.',
			'meta'     => array(
				SeoFields::TITLE       => 'Apprendre l’alphabet arabe facilement en 5 étapes',
				SeoFields::DESCRIPTION => 'Une méthode simple en 5 étapes pour apprendre l’alphabet arabe facilement : familles de lettres, voyelles, lettres non liées et lecture quotidienne.',
			),
			'content'  => ag_b_p( 'L’alphabet arabe impressionne souvent les débutants. Pourtant, avec une méthode claire, quelques semaines suffisent pour lire un texte vocalisé. Voici les cinq étapes que nous suivons avec nos élèves.' )
				. ag_b_h2( '1. Comprendre le fonctionnement de l’écriture' )
				. ag_b_p( 'L’arabe s’écrit et se lit de droite à gauche. L’alphabet compte 28 lettres, presque toutes des consonnes : les voyelles brèves sont notées par de petits signes placés au-dessus ou au-dessous des lettres. Il n’y a ni majuscules ni minuscules, mais la plupart des lettres changent légèrement de forme selon leur place dans le mot.' )
				. ag_b_h2( '2. Apprendre les lettres par familles' )
				. ag_b_p( 'De nombreuses lettres partagent la même forme de base et ne se distinguent que par leurs points. C’est le cas de ' . ag_b_ar( 'ب' ) . ' (bâ’), ' . ag_b_ar( 'ت' ) . ' (tâ’) et ' . ag_b_ar( 'ث' ) . ' (thâ’). Les apprendre ensemble divise l’effort de mémorisation.' )
				. ag_b_h2( '3. Maîtriser les voyelles et les signes' )
				. ag_b_list(
					array(
						'La <strong>fatha</strong> (son « a »), un petit trait au-dessus de la lettre : ' . ag_b_ar( 'بَ' ) . '.',
						'La <strong>kasra</strong> (son « i »), un petit trait au-dessous : ' . ag_b_ar( 'بِ' ) . '.',
						'La <strong>damma</strong> (son « ou »), un petit « waw » au-dessus : ' . ag_b_ar( 'بُ' ) . '.',
						'Le <strong>soukoun</strong>, qui indique l’absence de voyelle : ' . ag_b_ar( 'بْ' ) . '.',
						'La <strong>chadda</strong>, qui double la consonne : ' . ag_b_ar( 'بّ' ) . '.',
					)
				)
				. ag_b_h2( '4. Repérer les six lettres qui ne s’attachent pas' )
				. ag_b_p( 'Six lettres ne se lient jamais à la lettre qui les suit : ' . ag_b_ar( 'ا د ذ ر ز و' ) . '. Les connaître évite bien des hésitations : elles expliquent les « coupures » visibles à l’intérieur des mots.' )
				. ag_b_h2( '5. Lire tous les jours, à voix haute' )
				. ag_b_p( 'La lecture devient fluide par la répétition. Lisez chaque jour quelques lignes de texte vocalisé à voix haute, enregistrez-vous si possible, et faites corriger votre prononciation par un enseignant.' )
				. ag_b_callout( ag_b_p( 'En cours, ces étapes sont travaillées dans l’ordre, avec un enseignant qui corrige votre prononciation en direct. Découvrez nos <a href="/cours-arabe-en-ligne/">cours d’arabe en ligne</a>.' ) )
				. ag_b_h2( 'En résumé' )
				. ag_b_p( 'Comprendre le principe de l’écriture, regrouper les lettres, maîtriser les voyelles, repérer les lettres non liées et lire chaque jour : avec ces cinq étapes, <a href="/apprendre-arabe-facilement/">apprendre l’arabe facilement</a> devient un objectif réaliste.' ),
		),

		'choisir-cours-arabe-en-ligne' => array(
			'title'    => 'Cours d’arabe en ligne : 7 critères pour bien choisir',
			'category' => 'apprendre-arabe',
			'excerpt'  => 'Cours en direct ou en vidéo, taille des groupes, niveau de l’enseignant, progression : les critères qui font vraiment la différence pour progresser en arabe.',
			'meta'     => array(
				SeoFields::TITLE       => 'Cours d’arabe en ligne : 7 critères pour bien choisir',
				SeoFields::DESCRIPTION => 'Comment choisir ses cours d’arabe en ligne ? Direct, taille des groupes, enseignant, progression : les 7 critères essentiels et les pièges à éviter.',
			),
			'content'  => ag_b_p( 'Les offres de cours d’arabe en ligne se sont multipliées. Toutes ne se valent pas : voici les critères qui font réellement progresser, et les pièges à éviter.' )
				. ag_b_h2( 'Les 7 critères essentiels' )
				. ag_b_h3( '1. Des cours en direct' )
				. ag_b_p( 'Une langue s’apprend en la pratiquant. Les cours en direct vous font lire, répondre et corriger vos erreurs immédiatement, ce qu’aucune vidéo enregistrée ne permet.' )
				. ag_b_h3( '2. Des petits groupes' )
				. ag_b_p( 'Au-delà d’une dizaine d’élèves, chacun parle trop peu. Un petit groupe garantit que vous êtes sollicité à chaque séance.' )
				. ag_b_h3( '3. Un enseignant qualifié et pédagogue' )
				. ag_b_p( 'Maîtriser l’arabe ne suffit pas : il faut savoir l’expliquer à des francophones, avec patience et méthode.' )
				. ag_b_h3( '4. Une progression par niveaux' )
				. ag_b_p( 'Des niveaux définis, avec des objectifs clairs, permettent de mesurer vos progrès et de rester motivé.' )
				. ag_b_h3( '5. L’arabe littéraire comme base' )
				. ag_b_p( 'C’est la langue de l’écrit et des médias, comprise dans tout le monde arabe. Elle constitue le meilleur point de départ.' )
				. ag_b_h3( '6. Des supports adaptés aux francophones' )
				. ag_b_p( 'Des explications en français et des supports progressifs évitent de se perdre dans des manuels pensés pour d’autres publics.' )
				. ag_b_h3( '7. Un suivi entre les séances' )
				. ag_b_p( 'Exercices, corrections et conseils de révision font la différence entre ceux qui stagnent et ceux qui progressent.' )
				. ag_b_h2( 'Les pièges à éviter' )
				. ag_b_list(
					array(
						'Les promesses de « parler couramment en un mois ».',
						'Les cours uniquement en vidéo, sans aucune interaction.',
						'Les groupes trop nombreux où l’on ne prend jamais la parole.',
						'L’absence d’évaluation de niveau avant de commencer.',
					)
				)
				. ag_b_h2( 'Notre approche' )
				. ag_b_p( 'Nos <a href="/cours-arabe-en-ligne/">cours d’arabe en ligne</a> réunissent ces critères : des classes en direct, en petits groupes, avec une progression par niveaux, un suivi régulier et des classes séparées pour hommes et femmes.' ),
		),

		'quel-niveau-arabe'            => array(
			'title'    => 'Quel est mon niveau en arabe ? Les repères pour se situer',
			'category' => 'apprendre-arabe',
			'excerpt'  => 'Grand débutant, lecteur hésitant ou élève qui veut reprendre : quelques repères simples pour savoir par quel niveau commencer vos cours d’arabe.',
			'meta'     => array(
				SeoFields::TITLE       => 'Quel est mon niveau en arabe ? Les repères pour se situer',
				SeoFields::DESCRIPTION => 'Débutant, intermédiaire ou avancé ? Des repères concrets pour évaluer votre niveau en arabe et choisir le bon cours, de la lecture à l’expression.',
			),
			'content'  => ag_b_p( 'Commencer dans un niveau trop facile fait perdre du temps ; commencer trop haut décourage. Voici les repères que nous utilisons pour orienter nos élèves dans notre parcours en cinq niveaux.' )
				. ag_b_h2( 'Niveau 1 : vous partez de zéro, ou presque' )
				. ag_b_p( 'Vous ne connaissez pas l’alphabet, ou seulement quelques lettres, et vous ne savez pas encore lire un mot vocalisé. Le niveau 1 est fait pour vous : il commence par la première lettre.' )
				. ag_b_h2( 'Niveau 2 : vous lisez, mais comprenez peu' )
				. ag_b_list(
					array(
						'Vous lisez un texte vocalisé, même lentement.',
						'Vous connaissez quelques mots, mais pas leur fonctionnement dans la phrase.',
						'Vous ne savez pas encore vous présenter en arabe.',
					)
				)
				. ag_b_h2( 'Niveau 3 : vous construisez des phrases simples' )
				. ag_b_p( 'Vous savez vous présenter, vous connaissez le vocabulaire de base et la phrase nominale, mais la conjugaison et la phrase verbale restent floues.' )
				. ag_b_h2( 'Niveaux 4 et 5 : vous lisez déjà des textes' )
				. ag_b_p( 'Vous conjuguez les verbes courants et comprenez des textes simples. Le niveau 4 vous fait passer aux textes authentiques ; le niveau 5 vise l’aisance, à l’écrit comme à l’oral.' )
				. ag_b_h2( 'Un échange pour confirmer' )
				. ag_b_p( 'Ces repères donnent une première idée. Avant votre inscription, un court échange avec un enseignant confirme votre niveau et vous oriente vers le bon groupe. Découvrez nos <a href="/cours-arabe-en-ligne/">cours d’arabe en ligne</a> et consultez l’<a href="/emploi-du-temps/">emploi du temps</a> des classes.' ),
		),
	),

	// Brouillons : dates et détails à confirmer avant publication.
	'events'        => array(
		'reunion-information-en-ligne' => array(
			'title'   => 'Réunion d’information en ligne',
			'excerpt' => 'Découvrez nos formations et nos niveaux, posez vos questions à l’équipe pédagogique et trouvez le groupe qui vous correspond.',
			'meta'    => array(
				Fields::EVENT_DATE  => '2026-11-21',
				Fields::EVENT_START => '18:30',
				Fields::EVENT_END   => '19:30',
				Fields::EVENT_MODE  => 'online',
			),
		),
		'atelier-decouverte-alphabet'  => array(
			'title'   => 'Atelier découverte : lire ses premiers mots en arabe',
			'excerpt' => 'Une heure pour découvrir l’alphabet arabe et lire vos premiers mots avec un enseignant de l’institut. Ouvert aux grands débutants.',
			'meta'    => array(
				Fields::EVENT_DATE  => '2026-12-05',
				Fields::EVENT_START => '19:00',
				Fields::EVENT_END   => '20:00',
				Fields::EVENT_MODE  => 'online',
			),
		),
	),
);
