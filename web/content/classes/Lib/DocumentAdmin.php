<?php
class Lib_DocumentAdmin
{
    public $Document_Directory = '';
    public $Root_Document_Directory = '';
    public $Post_Link = '';
    public $Title = 'Document Admin';

    public function  __construct($dir = '', $title='Document Admin')
    {
        $this->SetDirectory($dir);
        $this->SetPostLink();
        $this->Title = $title;
    }

    public function Process()
    {
        if (Get('IU')) {
            if (!Post('DOCUMENTUPLOAD')) {
                $this->UploadDocumentForm();
            } else {
                $this->UploadDocument();
                if (!$this->HaveError()) {
                    $this->DisplayDocuments();
                } else {
                    $this->UploadDocumentForm();
                }
            }


        } elseif (Get('DELETE_DOCUMENT')) {
            $document = Get('DELETE_DOCUMENT');
            $this->DeleteDocument($document);
            $this->DisplayDocuments();

        } elseif (Get('RENAME_DOCUMENT')) {
            if (Post('RENAMEFILE_DOCUMENT')) {
                $this->RenameDocument(Get('RENAME_DOCUMENT'), Post('NEWNAME_DOCUMENT'));
                if (!$this->HaveError()) {
                    $this->DisplayDocuments();
                } else {
                    $this->RenameDocumentForm();
                }
            } else {
                $this->RenameDocumentForm();
            }
        } else {
            $this->DisplayDocuments();
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
        $this->Document_Directory = $dir;
        $this->Root_Document_Directory = $_SERVER['DOCUMENT_ROOT'] . $dir;
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

    public function UploadDocument()
    {
        
        $folder = Post('IMAGEDIR');
        
        $new_document_file = $_FILES['DocumentFile']['name'];
        $new_Document_size = $_FILES['DocumentFile']['size'];
        $temp_file      = $_FILES['DocumentFile']['tmp_name'];
        $new_document_file = str_replace(' ', '_', $new_document_file);
        $new_document_file = str_replace(',', '_', $new_document_file);
        $new_document_file = str_replace('&', 'and', $new_document_file);

        $root_new_file = $_SERVER['DOCUMENT_ROOT'] . $folder . '/' . $new_document_file;
        if (move_uploaded_file ($temp_file, $root_new_file)) {
            $pchange = chmod($root_new_file, 0666);
            AddFlash("FILE: <b>$folder/$new_document_file</b> was successfully uploaded!");
            if (!$pchange) {
                AddMessage('WARNING: PERMISSIONS NOT SET!');
            }
        } else {
            AddError('Your file could not be uploaded!' . "<br />[$new_document_file]</h2>");
        }
    }

    public function UploadDocumentForm()
    {
        $IMAGEDIR = Post('IMAGEDIR');
        $folders = GetFolders($this->Root_Document_Directory);
        
        foreach ($folders as $key => $folder) {
            $folders[$key] = $this->Document_Directory . '/' . $folder;
        }
        $folders[] = $this->Document_Directory;

        natcasesort($folders);

        if (count($folders) == 1 ) {
            $select = qq("<input type=`hidden` name=`IMAGEDIR` value=`{$folders[0]}` />");
        } elseif (count($folders) > 1 ) {
            $select = '<p><select name="IMAGEDIR">';
            foreach($folders as $idir) {
                $have = ($idir == $IMAGEDIR)? 'selected' : '';
                $select .= qq("<option value=`$idir` $have>$idir</option>");
            }
            $select .= '</select></p>';
        }

        print <<<LBL_IU
        <p style="text-align:center;"><a class="stdbuttoni" href="$this->Post_Link">Return</a></p>
        <p style="text-align:center; font-weight:bold; color:#000;">
          Upload a Document file:
        </p>
        <form action="$this->Post_Link;IU=1" method="post" enctype="multipart/form-data">
            $select
            <input type="hidden" name="MAX_FILE_SIZE" value="20480000" />
            <p style="text-align:center;">
            <input type="file" name="DocumentFile" size="60" /><br />
            <input type="submit" name="DOCUMENTUPLOAD" value="Upload Document" />
            </p>
        </form>
LBL_IU;
    }

    public function DeleteDocument($document)
    {
        if (unlink("$this->Root_Document_Directory/$document")) {
            AddFlash("FILE: <b>$this->Document_Directory/$document</b> was deleted!");
        } else {
            AddError("Could not delete [$this->Document_Directory/$document]");
        }
    }


    public function RenameDocument($old_name, $new_name)
    {
        if (!(file_exists("$this->Root_Document_Directory/$new_name"))) {
            $filename1  = "$this->Root_Document_Directory/$old_name";
            $filename2  = "$this->Root_Document_Directory/$new_name";
            if (rename($filename1, $filename2)) {
                AddFlash("FILE RENAMED: <b>$old_name</b> to <b>$new_name</b>");
            }
        } else {
            AddError('File: <b>' . $new_name . '</b> already exists.');
        }
    }

    public function RenameDocumentForm()
    {
        $RENAME_DOCUMENT = Get('RENAME_DOCUMENT');
        print <<<RENAMEDOCUMENTLABEL
          <p style="text-align:center;"><a class="stdbuttoni" href="$this->Post_Link">Return</a></p>
          <form action="$this->Post_Link;RENAME_DOCUMENT=$RENAME_DOCUMENT" method="post">
          <input type="hidden" name="OLDNAME_DOCUMENT" value="$RENAME_DOCUMENT">
          <table align="center" border="0" cellpadding="3"
            style="background-color:#fff; color:#000; border:1px solid #888; font-size:1.2em;">
          <tr>
            <td align="right">Old Filename:</td><td>$RENAME_DOCUMENT</td>
          </tr>
          <tr>
            <td align="right">New Filename:</td>
            <td><input type="text" name="NEWNAME_DOCUMENT" size="40" value="$RENAME_DOCUMENT" /></td>
          </tr>
            <td></td>
            <td><input type="Submit" name="RENAMEFILE_DOCUMENT" value="Rename File" /></td>
          </tr></table>
          </form>
RENAMEDOCUMENTLABEL;
    }


    public function DisplayDocuments()
    {
        printqn("

        <table id=`Document_ADMIN_TABLE` class=`TABLE_DISPLAY` cellspacing=`1` cellpadding=`5` align=`center` style=`white-space:nowrap;` >
        <tr>
            <th colspan=`5`>
                 <h3>$this->Title</h3>
                 <p style=`text-align:center;`><a class=`add_record` href=`$this->Post_Link;IU=1`>Upload New Document</a></p>

                 <p>Filter: <input type=`text` id=`documentfilter` size=`40` maxlength=`80` onkeyup=`
            var filter = this.value;
            filter = filter.toLowerCase();
            filter = filter.replace('/', '::');
            var check = false;
            var rowId = '';
            var i = 0;
            var table = document.getElementById('Document_ADMIN_TABLE');
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

        $files = GetDirectory($this->Root_Document_Directory);

        $count = 0;
        $even = false;

        foreach ($files as $fi) {
            if (!(eregi('.LCK', $fi))) {
                $Lfilename = $this->Document_Directory . "/$fi";
                $filename  = $this->Root_Document_Directory ."/$fi";
                $t = date("m\/d\/Y", filemtime($filename));
                $fsize = number_format(filesize($filename)/1000,1).'KB';
                $deletelink = "$this->Post_Link;DELETE_DOCUMENT=$fi";
                $count++;

                $row_id = str_replace('/', '::', strtolower($Lfilename));
                $row_id = str_replace($this->Document_Directory, '', $row_id);

                $class = ($even)? 'even' : 'odd';
                $even = !$even;

                $eq = EncryptQuery("filename=$Lfilename;expired=9999-99-99");

                $link = qqn("<a style=`text-decoration:none;` target=`_blank` href=`document_download?eq=$eq`>$fi</a>");
                printqn("
            <tr id=`Document_ROW_$row_id` class=`$class`>

                <td style=`padding:3px; white-space:nowrap;` align=`right`>
                    $count.
                </td>
                <td>
                    <b>$link</b>
                </td>
                <td align=`right`>
                    $fsize
                </td>
                <td>
                  <a class=`stdbuttoni` href=`$this->Post_Link;RENAME_DOCUMENT=$fi`>Rename</a>&nbsp;
                </td>
                <td>
                  <a class=`row_delete` href=`$deletelink`
                    onclick=`return confirm('Are you sure you want to delete [$fi]?')`></a>
                </td>
            </tr>
    ");
            }
        }
        print "</table>\n";
    }


}