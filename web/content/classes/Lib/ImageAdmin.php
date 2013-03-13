<?php
class Lib_ImageAdmin
{
    public $Image_Directory = '';
    public $Root_Image_Directory = '';
    public $Post_Link = '';
    public $Title = 'Image Admin';

    public function  __construct($dir = '', $title='Image Admin')
    {
        $this->SetDirectory($dir);
        $this->SetPostLink();
        $this->Title = $title;
    }

    public function Process()
    {
        if (Get('IU')) {
            if (!Post('IMAGEUPLOAD')) {
                $this->UploadImageForm();
            } else {
                $this->UploadImage();
                if (!$this->HaveError()) {
                    $this->DisplayImages();
                } else {
                    $this->UploadImageForm();
                }
            }


        } elseif (Get('DELETE_IMAGE')) {
            $image = Get('DELETE_IMAGE');
            $this->DeleteImage($image);
            $this->DisplayImages();

        } elseif (Get('RENAME_IMAGE')) {
            if (Post('RENAMEFILE_IMAGE')) {
                $this->RenameImage(Get('RENAME_IMAGE'), Post('NEWNAME_IMAGE'));
                if (!$this->HaveError()) {
                    $this->DisplayImages();
                } else {
                    $this->RenameImageForm();
                }
            } else {
                $this->RenameImageForm();
            }
        } else {
            $this->DisplayImages();
        }
    }

    public function HaveError()
    {
        global $PAGE;
        return ($PAGE['ERROR'] = '');
    }

    public function SetDirectory($dir)
    {
        if (substr($dir, 0, 1) != '/') {
            $dir = '/' . $dir;
        }
        $this->Image_directory = $dir;
        $this->Root_Image_Directory = $_SERVER['DOCUMENT_ROOT'] . $dir;
    }


    public function SetPostLink()
    {
        global $PAGE;
        if (!empty($PAGE)) {
            $this->Post_Link = $PAGE['pagelink'];
            if (Get('DIALOG')) {
                $this->Post_Link .= ';DIALOG=' . Get('DIALOG');
            }
        }

    }

    public function UploadImage()
    {
        $new_image_file = $_FILES['ImageFile']['name'];
        $new_image_size = $_FILES['ImageFile']['size'];
        $temp_file      = $_FILES['ImageFile']['tmp_name'];
        $new_image_file = str_replace(' ', '_', $new_image_file);

        if (move_uploaded_file ($temp_file, "$this->Root_Image_Directory/$new_image_file")) {
            $pchange = chmod("$this->Root_Image_Directory/$new_image_file", 0666);
            AddFlash("FILE: <b>$this->Image_Directory/$new_image_file</b> was successfully uploaded!");
            if (!$pchange) {
                AddMessage('WARNING: PERMISSIONS NOT SET!');
            }
        } else {
            AddError('Your file could not be uploaded!' . "<br />[$new_image_file]</h2>");
        }
    }

    public function UploadImageForm()
    {
        print <<<LBL_IU
        <p style="text-align:center;"><a class="stdbuttoni" href="$this->Post_Link">Return</a></p>
        <p style="text-align:center; font-weight:bold; color:#000;">
          Upload an Image file:
        </p>
        <form action="$this->Post_Link;IU=1" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="20480000" />
            <p style="text-align:center;">
            <input type="file" name="ImageFile" size="60" /><br />
            <input type="submit" name="IMAGEUPLOAD" value="Upload Image" />
            </p>
        </form>
LBL_IU;
    }

    public function DeleteImage($image)
    {
        if (unlink("$this->Root_Image_Directory/$image")) {
            AddFlash("FILE: <b>$this->Image_Directory/$image</b> was deleted!");
        } else {
            AddError("Could not delete [$this->Image_Directory/$image]");
        }
    }


    public function RenameImage($old_name, $new_name)
    {
        if (!(file_exists("$this->Root_Image_Directory/$new_name"))) {
            $filename1  = "$this->Root_Image_Directory/$old_name";
            $filename2  = "$this->Root_Image_Directory/$new_name";
            if (rename($filename1, $filename2)) {
                AddFlash("FILE RENAMED: <b>$old_name</b> to <b>$new_name</b>");
            }
        } else {
            AddError('File: <b>' . $new_name . '</b> already exists.');
        }
    }

    public function RenameImageForm()
    {
        $RENAME_IMAGE = Get('RENAME_IMAGE');
        print <<<RENAMEIMAGELABEL
          <p style="text-align:center;"><a class="stdbuttoni" href="$this->Post_Link">Return</a></p>
          <form action="$this->Post_Link;RENAME_IMAGE=$RENAME_IMAGE" method="post">
          <input type="hidden" name="OLDNAME_IMAGE" value="$RENAME_IMAGE">
          <table align="center" border="0" cellpadding="3"
            style="background-color:#fff; color:#000; border:1px solid #888; font-size:1.2em;">
          <tr>
            <td align="right">Old Filename:</td><td>$RENAME_IMAGE</td>
          </tr>
          <tr>
            <td align="right">New Filename:</td>
            <td><input type="text" name="NEWNAME_IMAGE" size="40" value="$RENAME_IMAGE" /></td>
          </tr>
            <td></td>
            <td><input type="Submit" name="RENAMEFILE_IMAGE" value="Rename File" /></td>
          </tr></table>
          </form>
RENAMEIMAGELABEL;
    }


    public function DisplayImages()
    {
        printqn("

        <table id=`IMAGE_ADMIN_TABLE` class=`TABLE_DISPLAY` cellspacing=`1` cellpadding=`5` align=`center` >
        <tr>
            <th colspan=`3`>
                 <h3>$this->Title</h3>
                 <p style=`text-align:center;`><a class=`add_record` href=`$this->Post_Link;IU=1`>Upload New Image</a></p>

                 <p>Filter: <input type=`text` id=`imagefilter` size=`40` maxlength=`80` onkeyup=`
            var filter = this.value;
            filter = filter.toLowerCase();
            filter = filter.replace('/', '::');
            var check = false;
            var rowId = '';
            var i = 0;
            var table = document.getElementById('IMAGE_ADMIN_TABLE');
            var rows = table.getElementsByTagName('tr');
            for (i in rows ) {
                rowId = rows[i].id;
                check = rowId.indexOf(filter)
                if (check > -1) {
                    showId(rowId);
                } else {
                    hideId(rowId);
                }
            }
        ` /></p>
            </th>
        </tr>");

        $files = GetDirectory($this->Root_Image_Directory, '.jpg,.png,.gif');

        $count = 0;

        foreach ($files as $fi) {
            if (!(eregi('.LCK',$fi))) {
                $Lfilename = $this->Image_directory . "/$fi";
                $filename  = $this->Root_Image_Directory ."/$fi";
                $t = date("m\/d\/Y", filemtime($filename));
                list($width, $height, $type, $attr) = getimagesize($filename);
                $viewwidth = min(200, $width);

                if($width > $height){
                $thumbwidth  = min(200, $width);
                $thumbheight = round($thumbwidth * $height/$width);
                } else {
                    $thumbheight  = min(200, $height);
                    $thumbwidth = round($thumbheight * $width/$height);
                }

                $margintop = $thumbheight+6;


                $fsize = number_format(filesize($filename)/1000,1).'KB';
                $deletelink = "$this->Post_Link;DELETE_IMAGE=$fi";
                $count++;

                if (($width > 200) or ($height > 200)) {

                    $imageout = <<<IMAGEOUT1
                <a class="imagelink" href="#" style="width:{$thumbwidth}px;" onclick="showId('picturediv$count'); return false;">
                <img src="$Lfilename" border="0" width="$thumbwidth" height="$thumbheight" alt="$Lfilename" />
                </a>
                <div id="picturediv$count" style="display:none; margin-top:-{$margintop}px; position:absolute;
                        margin-left : -2px; border : 2px solid #000;"
                    onclick="hideId('picturediv$count');">
                <img style="border:4px solid #fff;" src="$Lfilename" border="0" width="$width" height="$height" alt="$Lfilename" />
                </div>
IMAGEOUT1;
                } else {
                    $imageout =
                      qq("<img src=`$Lfilename` border=`1` width=`$thumbwidth` height=`$thumbheight` alt=`$Lfilename` />");
                }

                $row_id = str_replace('/', '::', strtolower($Lfilename));
                $row_id = str_replace($this->Image_directory, '', $row_id);

                printqn("
            <tr id=`IMAGE_ROW_$row_id`>
            <td width=`205`>
            $imageout
            </td>
            <td style=`padding:3px; white-space:nowrap;`>
                $count.
                <b>$this->Image_Directory/$fi</b>
                <div style=`font-size: 8pt; margin:3px 0px 3px 20px;`>
                    Version: $t &mdash; Width: $width&nbsp;&nbsp;Height: $height &mdash; Size: $fsize </div>
                <div style=`font-size:8pt; margin:10px;`>
                  <a class=`stdbuttoni` href=`$this->Post_Link;RENAME_IMAGE=$fi`>Rename</a>&nbsp;
                  <a class=`stdbuttoni` href=`$deletelink`
                    onclick=`return confirm('Are you sure you want to delete [$fi]?')`>Delete</a>
                </div>
            </td>
        </tr>
    ");
            }
        }
        print "</table>\n";
    }


}