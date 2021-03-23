<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseSendMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $title;
    protected $text;
    protected $file_pass;

    /**
     * Create a new message instance.
     * name = 仕入先名, file_name = 送信するファイルの名前
     *
     * @return void
     */
    public function __construct($name='', $text='{nocoverpage}', $file_name=NULL)
    {
    	// title = 件名, text = メール本文
    	// 件名を空、本文に『{nocoverpage}』と入れることで、送付状が送られなくなる
    	$this->title = '';
		$this->text = $text;
		//Khi đưa code lên server nhớ thay đổi app\\ thành a/. Nếu không sẽ gây ra lỗi
    	if($file_name <> NULL)$this->file_pass = 'app'.DIRECTORY_SEPARATOR.$file_name;
    	else $this->file_pass = NULL;
//    	$this->file_pass = 'app/テスト送信用.xlsx';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){
    	if($this->file_pass <> NULL){
    		return $this->text('emails.purchase_mail_text')
    					->subject($this->title)
    					->with([
    							'text' => $this->text,
    					])
    					->attach(storage_path($this->file_pass));
    	}
    	else{
    		return $this->text('emails.purchase_mail_text')
    					->subject($this->title)
    					->with([
    							'text' => $this->text,
    					]);
    	}
    }
}
