<?PHP


include_once("LabelText.php");


class LabelImage extends LabelText
{


    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
    	if($this->m_Width){
    		$width_str = " width=\"".$this->m_Width."\" ";
    	}
        if($this->m_Height){
    		$height_str = " height=\"".$this->m_Height."\" ";
    	}    	
    	if($this->m_Value){
        	$sHTML = "<img src=\"".$this->m_Value."\" $width_str $height_str />";
    	}
        return $sHTML;
    }

}

?>