<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Cloudbuster_ext
{
	public $settings = array();
	public $description = 'EE CMS plugin that busts caching on Cloudflare when entries or files are updated/uploaded.';
	public $docs_url = '';
	public $name = 'CloudBuster';
	public $settings_exist = 'y';
	public $version = '1.0.0';

	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		
		$this->settings = $settings;

		// Load file uploads directories

		$file_url_prefixes = array();

		foreach($this->EE->db->get('upload_prefs')->result_array() as $upload_pref)
		{
			$file_url_prefixes[$upload_pref['id']] = $upload_pref['url'];
		}

		$this->file_url_prefixes = $file_url_prefixes;
	}

	public function settings()
	{
		$settings = array();

		$settings['api_key'] = array('i', null, '');
		$settings['email'] = array('i', null, '');
		$settings['zone'] = array('i', null, '');

		return $settings;
	}

	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array(
			'api_key' => '',
			'email' => '',
			'zone' => ''
		);

		$this->EE->db->insert_batch('extensions', array(
			array(
				'class' => __CLASS__,
				'method' => 'entry_submission_end',
				'hook' => 'entry_submission_end',
				'settings' => serialize($this->settings),
				'version' => $this->version,
				'enabled' => 'y',
			),
			array(
				'class' => __CLASS__,
				'method' => 'file_after_save',
				'hook' => 'file_after_save',
				'settings' => serialize($this->settings),
				'version' => $this->version,
				'enabled' => 'y',
			),
			array(
				'class' => __CLASS__,
				'method' => 'files_after_delete',
				'hook' => 'files_after_delete',
				'settings' => serialize($this->settings),
				'version' => $this->version,
				'enabled' => 'y',
			),
		));
	}

	private function purge($urls)
	{
		if(!$this->settings['api_key'] || !$this->settings['email'] || !$this->settings['zone'])
		{
			return;
		}

		$headers = array(
			'Content-Type' => 'application/json',
			'X-Auth-Key' => $this->settings['api_key'],
			'X-Auth-Email' => $this->settings['email']
		);
		
		$urls = !is_array($urls) ? array($urls) : $urls;

		$ch = curl_init('https://api.cloudflare.com/client/v4/zones/' . $this->settings['zone'] . '/purge_cache');

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('files' => $urls)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);
		$result = json_decode($result);

		return $result;
	}

	public function entry_submission_end($entry, $meta, $data)
	{
		$urls = array();

		if(isset($data['structure__uri']))
		{
			$urls[] = $this->EE->config->item('site_url') . $data['structure__uri'];
		}
		elseif(isset($data['url_title']))
		{
			$urls[] = $this->EE->config->item('site_url') . $data['url_title'];
		}

		$this->purge($urls);
	}

	public function file_after_save($file_id, $data)
	{
		$file = $this->EE->db->where('file_id', $file_id)->get('files')->result_array();

		$this->purge($this->file_url_prefixes[$file['file_name']]);
	}

	public function files_after_delete($deleted)
	{
		$urls = array();

		foreach($deleted as $row)
		{
			$urls[] = $this->file_url_prefixes[$row->file_name];
		}

		$this->purge($urls);
	}

	public function disable_extension()
	{
		$this->EE->db->delete('extensions', array('class' => __CLASS__));
	}

	public function update_extension($current = '')
	{
		if($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => __CLASS__));
	}
}

/* End of file ext.cloudbuster.php */
/* Location: /system/expressionengine/third_party/cloudbuster/ext.cloudbuster.php */