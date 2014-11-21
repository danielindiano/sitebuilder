<?php
namespace meumobi\sitebuilder\services;
use meumobi\sitebuilder\entities\Visitor;
use meumobi\sitebuilder\repositories\VisitorsRepository;

class ImportVisitorsCsvService extends ImportCsvService {
	const LOG_CHANNEL = 'sitebuilder.import_visitors_csv';

	protected $site;

	public function call() {
		//TODO implement service call
	}

	public function import()
	{
		$startTime = time();
		$imported = 0;
		$repo = new VisitorsRepository();
		while ($data = $this->getNextItem()) {
			$data['password'] = 'infobox';//TODO auto generate and send the password by email
			$data['site_id'] = $this->getSite()->id;
			$data['groups'] = explode(',', $data['groups']);
			$visitor = new Visitor($data);
			$repo->create($visitor);
			$imported++;
		}
		fclose($this->getFile());
		//unlink($this->filePath);
		return $imported;
	}
	
	public function setSite(\Sites $site)
	{
		$this->site = $site;
	}
	
	public function getSite()
	{
		if (!$this->site) {
			throw new \Exception("site not set");
		}
		return $this->site;
	}
}
