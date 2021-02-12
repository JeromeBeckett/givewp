<?php

namespace Give\Framework\Migrations;

use Exception;
use Give\Framework\Migrations\Contracts\Migration;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogRepository;
use Give\MigrationLog\MigrationLogStatus;

/**
 * Class MigrationsRunner
 *
 * @since 2.9.0
 */
class MigrationsRunner {
	/**
	 * List of completed migrations.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private $completedMigrations;

	/**
	 * @since 2.9.0
	 *
	 * @var MigrationsRegister
	 */
	private $migrationRegister;

	/**
	 * @since 2.10.0
	 *
	 * @var MigrationLogFactory
	 */
	private $migrationLogFactory;

	/**
	 * @since 2.10.0
	 * @var MigrationLogRepository
	 */
	private $migrationLogRepository;

	/**
	 *  MigrationsRunner constructor.
	 *
	 * @param  MigrationsRegister  $migrationRegister
	 * @param  MigrationLogFactory  $migrationLogFactory
	 * @param  MigrationLogRepository  $migrationLogRepository
	 */
	public function __construct(
		MigrationsRegister $migrationRegister,
		MigrationLogFactory $migrationLogFactory,
		MigrationLogRepository $migrationLogRepository
	) {
		$this->migrationRegister      = $migrationRegister;
		$this->migrationLogFactory    = $migrationLogFactory;
		$this->migrationLogRepository = $migrationLogRepository;
		$this->completedMigrations    = $this->migrationLogRepository->getCompletedMigrationsIDs();
	}

	/**
	 * Run database migrations.
	 *
	 * @since 2.9.0
	 */
	public function run() {
		global $wpdb;

		if ( ! $this->hasMigrationToRun() ) {
			return;
		}

		// Store and sort migrations by timestamp
		$migrations = [];

		foreach ( $this->migrationRegister->getMigrations() as $migrationClass ) {
			/* @var Migration $migrationClass */
			$migrations[ $migrationClass::timestamp() . '_' . $migrationClass::id() ] = $migrationClass;
		}

		ksort( $migrations );

		$migrationOrder = 1;

		foreach ( $migrations as $migrationClass ) {
			$migrationId = $migrationClass::id();

			if ( in_array( $migrationId, $this->completedMigrations, true ) ) {
				continue;
			}

			$migrationLog = $this->migrationLogFactory->make( $migrationId );

			// Begin transaction
			$wpdb->query( 'START TRANSACTION' );

			try {
				/** @var Migration $migration */
				$migration = give( $migrationClass );

				$migration->run();

				// Save migration status
				$migrationLog->setStatus( MigrationLogStatus::SUCCESS );
				$migrationLog->setRunOrder( $migrationOrder );
				$migrationLog->save();
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK' );

				$migrationLog->setStatus( MigrationLogStatus::FAILED );
				$migrationLog->setRunOrder( $migrationOrder );
				$migrationLog->setError( $exception );
				$migrationLog->save();

				give()->notices->register_notice(
					[
						'id'          => 'migration-failure',
						'description' => sprintf(
							'%1$s <a href="https://givewp.com/support/">https://givewp.com/support</a>',
							esc_html__( 'There was a problem running the migrations. Please reach out to GiveWP support for assistance:', 'give' )
						),
					]
				);

				break;
			}

			// Commit transaction if successful
			$wpdb->query( 'COMMIT' );

			$migrationOrder++;
		}
	}

	/**
	 * Return whether or not all migrations completed.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function hasMigrationToRun() {
		return (bool) array_diff( $this->migrationRegister->getRegisteredIds(), $this->completedMigrations );
	}
}
