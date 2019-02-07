<?php

abstract class CSR_Model {

	/**
	 * Return a attribute
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}
	}

	/**
	 * 全プロパティのキーを取得
	 *
	 * @return array
	 */
	public function get_permit_keys() {
		$vars = get_object_vars( $this );
		return array_keys( $vars );
	}

	/**
	 * 全プロパティを取得
	 *
	 * @return array
	 */
	public function gets() {
		$properties = [];
		$permit_keys = $this->get_permit_keys();
		foreach ( $permit_keys as $permit_key ) {
			$properties[ $permit_key ] = $this->$permit_key;
		}
		return $properties;
	}
}
