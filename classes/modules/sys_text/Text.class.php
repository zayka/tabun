<?
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

require_once('classes/lib/external/Jevix/jevix.class.php');
require_once('classes/lib/external/geshi/geshi.php');

/**
 * Модуль обработки текста на основе типографа Jevix
 *
 */
class Text extends Module {
	/**
	 * Объект типографа
	 *
	 * @var Jevix
	 */
	protected $oJevix;		
	
	/**
	 * Инициализация модуля
	 *
	 */
	public function Init() {	
		/**
		 * Создаем объект типографа и запускаем его конфигурацию
		 */
		$this->oJevix = new Jevix();		
		$this->JevixConfig();			
	}
	
	/**
	 * Конфигурирует типограф
	 *
	 */
	protected function JevixConfig() {
		// Разрешённые теги
		$this->oJevix->cfgAllowTags(array('cut','a', 'img', 'i', 'b', 'u', 's', 'video', 'em',  'strong', 'nobr', 'li', 'ol', 'ul', 'sup', 'abbr', 'sub', 'acronym', 'h4', 'h5', 'h6', 'br', 'hr', 'pre', 'code', 'object', 'param', 'embed'));
		// Коротие теги типа
		$this->oJevix->cfgSetTagShort(array('br','img', 'hr', 'cut'));
		// Преформатированные теги
		$this->oJevix->cfgSetTagPreformatted(array('pre','code'));
		// Разрешённые параметры тегов		
		$this->oJevix->cfgAllowTagParams('img', array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int'));
		$this->oJevix->cfgAllowTagParams('a', array('title', 'href'));		
		$this->oJevix->cfgAllowTagParams('cut', array('name'));
		$this->oJevix->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => '#link'));
		$this->oJevix->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
		$this->oJevix->cfgAllowTagParams('embed', array('src' => '#image', 'type' => '#text','allowscriptaccess' => '#text', 'allowfullscreen' => '#text','width' => '#int', 'height' => '#int'));
		// Параметры тегов являющиеся обязательными
		$this->oJevix->cfgSetTagParamsRequired('img', 'src');
		$this->oJevix->cfgSetTagParamsRequired('a', 'href');
		// Теги которые необходимо вырезать из текста вместе с контентом
		$this->oJevix->cfgSetTagCutWithContent(array('script', 'iframe', 'style'));
		// Вложенные теги
		$this->oJevix->cfgSetTagChilds('ul', array('li'), false, true);
		$this->oJevix->cfgSetTagChilds('ol', array('li'), false, true);
		$this->oJevix->cfgSetTagChilds('object', 'param', false, true);
		$this->oJevix->cfgSetTagChilds('object', 'embed', false, true);
		// Если нужно оставлять пустые не короткие теги
		$this->oJevix->cfgSetTagIsEmpty(array('param','embed'));
		// Отключение авто-добавления <br>
		//$this->oJevix->cfgSetAutoBrMode(false);
		// Автозамена
		$this->oJevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)', '(C)', '(R)'), array('±', '©', '®', '©', '®'));
		//$this->oJevix->cfgSetXHTMLMode(false);
		$this->oJevix->cfgSetTagNoTypography('code');

	}
	
	/**
	 * Парсинг текста с помощью Jevix
	 *
	 * @param string $sText
	 * @param array $aError
	 * @return string
	 */
	public function JevixParser($sText,&$aError=null) {		
		$sResult=$this->oJevix->parse($sText,$aError);
		return $sResult;
	}
	
	/**
	 * Парсинг текста на предмет видео
	 *
	 * @param string $sText
	 * @return string
	 */
	public function VideoParser($sText) {	
		/**
		 * youtube.com
		 */
		$sResult = preg_replace('/<video>http:\/\/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/$1&hl=en"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$1&hl=en" type="application/x-shockwave-flash" wmode="transparent" width="425" height="344"></embed></object>', $sText);
		$sResult = preg_replace('/<video>http:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/$1&hl=en"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$1&hl=en" type="application/x-shockwave-flash" wmode="transparent" width="425" height="344"></embed></object>', $sResult);		
		/**
		 * rutube.ru
		 */
		$sResult = preg_replace('/<video>http:\/\/rutube.ru\/tracks\/\d+.html\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<OBJECT width="470" height="353"><PARAM name="movie" value="http://video.rutube.ru/$1"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><PARAM name="flashVars" value="uid=662118"></PARAM><EMBED src="http://video.rutube.ru/$1" type="application/x-shockwave-flash" wmode="window" width="470" height="353" allowFullScreen="true" flashVars="uid=662118"></EMBED></OBJECT>', $sResult);
		$sResult = preg_replace('/<video>http:\/\/www\.rutube.ru\/tracks\/\d+.html\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<OBJECT width="470" height="353"><PARAM name="movie" value="http://video.rutube.ru/$1"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><PARAM name="flashVars" value="uid=662118"></PARAM><EMBED src="http://video.rutube.ru/$1" type="application/x-shockwave-flash" wmode="window" width="470" height="353" allowFullScreen="true" flashVars="uid=662118"></EMBED></OBJECT>', $sResult);				
		return $sResult;
	}
	
	/**
	 * Подцветка кода
	 *
	 * @param string $sText
	 * @return string
	 */
	public function GeshiParser($sText) {
		$sTextTemp=str_replace("\r\n",'[!rn!]',$sText);
		$sTextTemp=str_replace("\n",'[!n!]',$sTextTemp);
		if (preg_match_all("/<code>(.*)<\/code>/Ui",$sTextTemp,$aMatch,PREG_SET_ORDER)) {
			$oGeshi = new GeSHi('','php');
			$oGeshi->set_header_type(GESHI_HEADER_DIV);
			$oGeshi->enable_classes();
			$oGeshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0;', false);
			$oGeshi->set_line_style('color: #003030;', 'font-weight: bold; color: #006060;', true);
			$oGeshi->set_code_style('color: #000020;', true);
			$oGeshi->enable_keyword_links(false);
			$oGeshi->set_link_styles(GESHI_LINK, 'color: #000060;');
			$oGeshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
			foreach ($aMatch as $aCode) {
				$sCode=html_entity_decode($aCode[1]);
				$sCode=str_replace("[!rn!]","\r\n",$sCode);
				$sCode=str_replace("[!n!]","\n",$sCode);
				$oGeshi->set_source($sCode);
				$sCodeGeshi=$oGeshi->parse_code();
				$sTextTemp=str_replace($aCode[0],$sCodeGeshi,$sTextTemp);
			}
			$sTextTemp=str_replace("[!rn!]","\r\n",$sTextTemp);
			$sTextTemp=str_replace("[!n!]","\n",$sTextTemp);
			$sTextTemp='<style type="text/css">'.$oGeshi->get_stylesheet(true).'</style>'."\r\n".$sTextTemp;
			return $sTextTemp;
		}
		return $sText;
	}
	
	/**
	 * Парсит текст
	 *
	 * @param string $sText
	 */
	public function Parser($sText) {
		$sResult=$this->FlashParamParser($sText);
		$sResult=$this->JevixParser($sResult);	
		$sResult=$this->VideoParser($sResult);		
		$sResult=$this->GeshiParser($sResult);
		return $sResult;
	}
	/**
	 * Заменяет все вхождения короткого тега <param/> на длиную версию <param></param>
	 * 
	 */
	protected function FlashParamParser($sText) {		
		if (preg_match_all("@(<\s*param\s*name\s*=\s*\".*\"\s*value\s*=\s*\".*\")\s*/\s*>@Ui",$sText,$aMatch)) {				
			foreach ($aMatch[1] as $key => $str) {
				$str_new=$str.'></param>';				
				$sText=str_replace($aMatch[0][$key],$str_new,$sText);				
			}	
		}		
		return $sText;
	}
}
?>