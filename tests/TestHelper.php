<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2022
 */


class TestHelper
{
	private static $aimeos;
	private static $config;
	private static $context = [];
	private static $dbm;


	/**
	 * Initializes the environment
	 */
	public static function bootstrap()
	{
		self::getAimeos();
		\Aimeos\MShop::cache( false );
	}


	/**
	 * Returns the context object
	 *
	 * @param string $site Site code
	 * @return \Aimeos\MShop\Context\Item\Iface Context object
	 */
	public static function context( $site = 'unittest' )
	{
		if( !isset( self::$context[$site] ) ) {
			self::$context[$site] = self::createContext( $site );
		}

		return clone self::$context[$site];
	}


	/**
	 * Returns the Aimeos bootstrap object
	 *
	 * @return \Aimeos\Bootstrap Aimeos bootstrap object
	 */
	private static function getAimeos()
	{
		if( !isset( self::$aimeos ) )
		{
			require_once dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'Bootstrap.php';
			self::$aimeos = new \Aimeos\Bootstrap( [], false );
		}

		return self::$aimeos;
	}


	/**
	 * Returns the configuration object
	 *
	 * @return \Aimeos\Base\Config\Iface Configuration object
	 */
	public static function getConfig()
	{
		if( !isset( self::$config ) ) {
			self::$config = self::createConfig();
		}

		return self::$config;
	}


	/**
	 * Returns the database manager object
	 *
	 * @return \Aimeos\Base\DB\Manager\Iface Database manager object
	 */
	public static function getDBManager()
	{
		return \Aimeos\Base\DB\Factory::create( self::getConfig(), 'DBAL' );
	}


	/**
	 * Creates a new configuration object
	 *
	 * @return \Aimeos\Base\Config\Iface Configuration object
	 */
	private static function createConfig()
	{
		$path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'config';
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'confdoc.ser';

		$object = new \Aimeos\Base\Config\PHPArray( [], $path );
		$object = new \Aimeos\Base\Config\Decorator\Documentor( $object, $file );

		return $object;
	}


	/**
	 * Creates a new context object
	 *
	 * @param string $site Site code
	 * @return \Aimeos\MShop\Context\Item\Iface Context object
	 */
	private static function createContext( $site )
	{
		$ctx = new \Aimeos\MShop\Context\Item\Standard();
		$aimeos = self::getAimeos();


		$paths = $aimeos->getConfigPaths();
		$paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'config';
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'confdoc.ser';

		$conf = new \Aimeos\Base\Config\PHPArray( [], $paths );
		$conf = new \Aimeos\Base\Config\Decorator\Memory( $conf );
		$conf = new \Aimeos\Base\Config\Decorator\Documentor( $conf, $file );
		$ctx->setConfig( $conf );


		$logger = new \Aimeos\Base\Logger\File( $site . '.log', \Aimeos\Base\Logger\Iface::DEBUG );
		$ctx->setLogger( $logger );


		$dbm = new \Aimeos\Base\DB\Manager\PDO( $conf );
		$ctx->setDatabaseManager( $dbm );


		$fs = new \Aimeos\Base\Filesystem\Manager\Standard( $conf->get( 'resource' ) );
		$ctx->setFilesystemManager( $fs );


		$mq = new \Aimeos\MW\MQueue\Manager\Standard( $conf );
		$ctx->setMessageQueueManager( $mq );


		$cache = new \Aimeos\MW\Cache\None();
		$ctx->setCache( $cache );


		$i18n = new \Aimeos\Base\Translation\None( 'de' );
		$ctx->setI18n( array( 'de' => $i18n ) );


		$session = new \Aimeos\Base\Session\None();
		$ctx->setSession( $session );


		$mail = new \Aimeos\Base\Mail\None();
		$ctx->setMail( $mail );


		$view = self::createView( $conf );
		$ctx->setView( $view );


		$localeManager = \Aimeos\MShop\Locale\Manager\Factory::create( $ctx );
		$locale = $localeManager->bootstrap( $site, 'de', '', false );
		$ctx->setLocale( $locale );


		$ctx->setEditor( 'core:lib/mshoplib' );

		return $ctx;
	}


	/**
	 * Creates a new view object
	 *
	 * @param \Aimeos\Base\Config\Iface $config Configuration object
	 * @return \Aimeos\MW\View\Iface View object
	 */
	protected static function createView( \Aimeos\Base\Config\Iface $config )
	{
		$tmplpaths = array_merge_recursive(
			self::getAimeos()->getCustomPaths( 'lib/custom/templates' ),
			self::getAimeos()->getCustomPaths( 'client/html/templates' ),
			self::getAimeos()->getCustomPaths( 'controller/jobs/templates' )
		);

		$view = new \Aimeos\MW\View\Standard( $tmplpaths );

		$trans = new \Aimeos\Base\Translation\None( 'de_DE' );
		$helper = new \Aimeos\MW\View\Helper\Translate\Standard( $view, $trans );
		$view->addHelper( 'translate', $helper );

		$helper = new \Aimeos\MW\View\Helper\Url\Standard( $view, 'http://baseurl' );
		$view->addHelper( 'url', $helper );

		$helper = new \Aimeos\MW\View\Helper\Number\Standard( $view, '.', '' );
		$view->addHelper( 'number', $helper );

		$helper = new \Aimeos\MW\View\Helper\Date\Standard( $view, 'Y-m-d' );
		$view->addHelper( 'date', $helper );

		$helper = new \Aimeos\MW\View\Helper\Config\Standard( $view, $config );
		$view->addHelper( 'config', $helper );

		return $view;
	}
}
