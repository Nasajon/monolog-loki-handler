<?php


namespace Er1z\MonologLokiHandler;


use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class LokiHandler extends AbstractProcessingHandler
{

    /**
     * @var string
     */
    protected $entrypoint;

    /**
     * @var string
     */
    protected $channel;

    public function __construct(string $entrypoint, string $channel, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->entrypoint = $this->getEntrypoint($entrypoint);
        $this->channel = $channel;
    }

    protected function getDefaultFormatter()
    {
        return new LokiFormatter();
    }


    private function getEntrypoint(string $entrypoint): string
    {
        if(substr($entrypoint, -1) != '/'){
            return $entrypoint;
        }

        return substr($entrypoint, 0, -1);
    }

    private function sendPacket(array $packet)
    {
        $payload = @json_encode($packet);
        $url = sprintf('%s/loki/api/v1/push', $this->entrypoint);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload))
        );

        curl_exec($ch);
    }

    /**
     * @inheritDoc
     */
    protected function write(array $record)
    {
        $payload = [
            'streams'=>[
                $record['formatted']
            ]
        ];
        
        //Sobrescreve o channel padrão (app)
        $payload['streams'][0]['stream']['channel'] = $this->channel;

        $this->sendPacket($payload);
    }

    public function handleBatch(array $records)
    {
        $rows = [];
        foreach($records as $record){
            $record = $this->processRecord($record);
            $rows[] = $this->getFormatter()->format($record);
        }

        $payload = [
            'streams'=>$rows
        ];

        $this->sendPacket($payload);
    }

}
