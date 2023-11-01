<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramWordCount;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Pishgaman\Pishgaman\Library\Virastyar\VirastyarInterface;
use Pishgaman\Pishgaman\Library\globalfunction;
use Carbon\Carbon;

class TelegramWordRepository
{
    protected $globalfunction;
    protected $TelegramMessage;

    public function __construct()
    {
        $this->globalfunction = new globalfunction();
        $this->TelegramMessage = new TelegramMessageRepository();
    }

    public function CountTelegramWordMessage()
    {
        $Virastyar = \App::make(VirastyarInterface::class);
        $options = [
            'min' => 0,
            'max' => 20001,
            'get' => true,
            'conditions' => [
                ['column' => 'id', 'operator' => '>', 'value' => 0],
                ['column' => 'id', 'operator' => '<', 'value' => 10000],
            ],            
        ];
        
        $messages = $this->TelegramMessage->TelegramMessageGet($options);

        foreach ($messages as $key => $value) {
            $cleanedText = preg_replace('/[.,;:!?()\[\]{}<>"\']/', '', $value->message);
            $cleanedText = strtolower($cleanedText);
            $cleanedText = $Virastyar->halfSpace($cleanedText,true);
            $words = preg_split('/\s+/', $cleanedText, -1, PREG_SPLIT_NO_EMPTY);
            $wordCounts = array_count_values($words);    
    
            foreach ($wordCounts as $key => $item) {
                $key = $this->preprocessText($key);

                // اگر کلمه شکلک است یا URL معتبر است یا حاوی "#" باشد، آن را نادیده بگیر
                if ($this->globalfunction->isEmoji($key) || strpos($key, '#') !== false) {
                    continue;
                }
    
                if ($key == '' || $key == null || $key == ' ') {
                    continue;
                }
    
                $messageDate = Carbon::parse($value->date);
                $this->createWordCount($value->id, $key, $item, $messageDate);
            }
        }
    }    

    public function preprocessText($text) {
    
        // الگوی حذف تمام علائم نگارشی
        $punctuationPattern = '/[.,،؛;:!?«»()\[\]{}<>"\']+/u';
        $text = preg_replace($punctuationPattern, '', $text);

        $word1 = ['و','ه','-','_','@','#'];
        $word2 = ['پس','او','را','به','از','نه','ان','آن','یا','هر','ند','در','که','با','بر','تا','ما','شد'];
        $word3 = ['خود','حتی','گفت','این','بود','شده','اگر','است','شود'];
        $word4 = ['باید','نیست','کنند','آنها','آن‌ها','همهٔ'];
        $word5 = ['هستند'];
        $word6 = [];
        $word7 = ['می‌کنند'];     
            
        $length = mb_strlen($text, 'UTF-8');

        switch ($length) {
            case 1 :
                $text = str_replace($word1, "", $text);
                break;
            case 2 :
                $text = str_replace($word2, "", $text);                
                break;
            case 3 :
                $text = str_replace($word3, "", $text);                
                break;  
            case 4 :
                $text = str_replace($word4, "", $text);                
                break;  
            case 5 :
                $text = str_replace($word5, "", $text);                
                break; 
            case 6 :
                $text = str_replace($word6, "", $text);                
                break; 
            case 7 :
                $text = str_replace($word7, "", $text);                
                break;                                                                             
        }

        // حذف افعال غیرضروری
        // $text = str_replace($verbsToRemove, "", $text);
    
        return $text;
    }


    public function createWordCount($id,$word, $count,$message_date)
    {
        if(!$this->globalfunction->isValidURL($word))
        {
            TelegramWordCount::create([
                'message_id'=>$id,
                'word' => $word,
                'word_count' => $count,
                'message_date' => $message_date
            ]);
        }        
    }
}
