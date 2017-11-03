<?php

    require('includes/classes/image.php');
    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }

    class toC_Json_Content
    {
        function getImages()
        {
            global $toC_Json, $osC_Database, $osC_Session;

            $osC_Image = new osC_Image_Admin();

            $records = array();

            if (isset($_REQUEST['content_id']) && is_numeric($_REQUEST['content_id'])) {
                $Qimages = $osC_Database->query('select id, image from :table_content_images where content_id = :content_id order by sort_order');
                $Qimages->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                $Qimages->bindInt(':content_id', $_REQUEST['content_id']);
                $Qimages->execute();

                while ($Qimages->next()) {
                    $records[] = array('id' => $Qimages->valueInt('id'),
                                       'image' => '<img src="' . DIR_WS_HTTP_CATALOG . 'images/content/originals/' . $Qimages->value('image') . '" border="0" />',
                                       'name' => $Qimages->value('image'),
                                       'size' => number_format(@filesize(DIR_FS_CATALOG . DIR_WS_IMAGES . 'content/originals/' . $Qimages->value('image'))) . ' bytes');
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';

                $osC_DirectoryListing = new osC_DirectoryListing($image_path, true);
                $osC_DirectoryListing->setIncludeDirectories('false');

                foreach ($osC_DirectoryListing->getFiles() as $file) {
                    $records[] = array('id' => '',
                                       'image' => '<img src="' . $image_path . $file['name'] . '" border="0" width="' . $osC_Image->getWidth('mini') . '" height="' . $osC_Image->getHeight('mini') . '" />',
                                       'name' => $file['name'],
                                       'size' => number_format($file['size']) . ' bytes');
                }
            }

            $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function uploadImage()
        {
            global $toC_Json, $osC_Database, $osC_Session, $osC_Language;

            $osC_Image = new osC_Image_Admin();

            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

            if (is_array($_FILES)) {
                $content_images = array_keys($_FILES);
                $content_images = $content_images[0];
            }

            $content_images = new upload($content_images);
            if (isset($_REQUEST['content_id']) && $_REQUEST['content_id'] > 0) {
                if ($content_images->exists()) {
                    $image_path = '../images/content/originals/';
                    $content_images->set_destination($image_path);

                    if ($content_images->parse() && $content_images->save()) {
                        $Qimage = $osC_Database->query('insert into :table_content_images (content_id, image, sort_order, date_added,content_type) values (:content_id, :image, :sort_order, :date_added,:content_type)');
                        $Qimage->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                        $Qimage->bindInt(':content_id', $_REQUEST['content_id']);
                        $Qimage->bindValue(':image', $content_images->filename);
                        $Qimage->bindValue(':content_type', $_REQUEST['content_type']);
                        $Qimage->bindInt(':sort_order', 0);
                        $Qimage->bindRaw(':date_added', 'now()');
                        $Qimage->execute();

                        if (!$osC_Database->isError()) {
                            $image_id = $osC_Database->nextID();
                            $new_image_name = $_REQUEST['content_id'] . '_' . $image_id . '_' . $content_images->filename;
                            @rename($image_path . $content_images->filename, $image_path . $new_image_name);

                            $Qupdate = $osC_Database->query('update :table_content_images set image = :image where id = :id');
                            $Qupdate->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                            $Qupdate->bindValue(':image', $new_image_name);
                            $Qupdate->bindInt(':id', $image_id);
                            $Qupdate->execute();

                            if (!$osC_Database->isError()) {
                                foreach ($osC_Image->getGroups() as $group) {
                                    if ($group['id'] != '1') {
                                        if($osC_Image->resize($new_image_name, $group['id'], 'content') != true)
                                        {
                                            $response['success'] = false;
                                            $response['feedback'] = "Cannot resize img !!!";
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $response['success'] = false;
                                $response['feedback'] = $osC_Database->error;
                            }
                        }
                        else
                        {
                            $response['success'] = false;
                            $response['feedback'] = $osC_Database->error;
                        }
                    }
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';
                toc_mkdir($image_path);

                if ($content_images->exists()) {
                    $content_images->set_destination($image_path);
                    $content_images->parse();
                    $content_images->save();
                }
            }

            header('Content-Type: text/html');

            echo $toC_Json->encode($response);
        }

        function deleteImage()
        {
            global $toC_Json, $osC_Language, $osC_Session;

            $error = false;

            if (is_numeric($_REQUEST['image'])) {
                $osC_Image = new osC_Image_Admin();

                if (!$osC_Image->delete($_REQUEST['image'], TABLE_CONTENT_IMAGES, 'content')) {
                    $error = true;
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';

                if (!osc_remove($image_path . $_REQUEST['image'])) {
                    $error = true;
                }
            }

            if ($error === false) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function listDocuments()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qdocuments = $osC_Database->query("select d.* from :table_content_documents d where d.content_id = :content_id and d.content_type = :content_type order by d.documents_name");

            $Qdocuments->bindInt(':content_id', $content_id);
            $Qdocuments->bindInt(':content_type', $content_type);
            $Qdocuments->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
            $Qdocuments->execute();

            while ($Qdocuments->next()) {
                $entry_icon = osc_icon_from_filename($Qdocuments->value('filename'));
                $url = '../cache/documents/' . $Qdocuments->value('cache_filename');
                $action = array(
                    array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download')),
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('documents_id' => $Qdocuments->valueInt('documents_id'),
                                   'icon' => $entry_icon,
                                   'action' => $action,
                                   'url' => $url,
                                   'documents_name' => $Qdocuments->value('documents_name'),
                                   'documents_cache_filename' => $Qdocuments->value('cache_filename'),
                                   'documents_filename' => $Qdocuments->value('filename'),
                                   'documents_status' => $Qdocuments->value('documents_status'),
                                   'documents_description' => $Qdocuments->value('documents_description'));
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qdocuments->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listComments()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qcomments = $osC_Database->query("select c.*,u.image_url,a.user_name from :table_content_comments c inner join :table_users u on (c.created_by = u.administrators_id) inner join :table_administrators a on (c.created_by = a.id) where c.content_id = :content_id and c.content_type = :content_type order by c.comments_date_added desc");

            $Qcomments->bindInt(':content_id', $content_id);
            $Qcomments->bindValue(':content_type', $content_type);
            $Qcomments->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
            $Qcomments->bindTable(':table_users', TABLE_USERS);
            $Qcomments->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qcomments->setExtBatchLimit($start, $limit);
            $Qcomments->execute();

            while ($Qcomments->next()) {
                //$image = '<img src="../images/users/mini/' . $Qcomments->value('image_url') . '" width="100" height="80" />';
                $image = '<img src="../images/users/mini/no-image.png" width="100" height="80" />';
                $action = array(
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('comments_id' => $Qcomments->valueInt('comments_id'),
                                   'action' => $action,
                                   'image_url' => $image,
                                   'comment' => array('user_name' => $Qcomments->value('user_name'), 'comments_description' => $Qcomments->value('comments_description')),
                                   'comments_date_added' => $Qcomments->value('comments_date_added'),
                                   'comments_status' => $Qcomments->value('comments_status'));
            }

            $Qcomments->freeResult();

            $response = array(EXT_JSON_READER_TOTAL => $Qcomments->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listLinks()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qlinks = $osC_Database->query("select l.* from :table_content_links l where l.content_id = :content_id and l.content_type = :content_type order by l.links_name");

            $Qlinks->bindInt(':content_id', $content_id);
            $Qlinks->bindInt(':content_type', $content_type);
            $Qlinks->bindTable(':table_content_links', TABLE_CONTENT_LINKS);
            $Qlinks->execute();

            while ($Qlinks->next()) {
                $url = $Qlinks->value('links_url');
                $action = array(
                    array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download')),
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('links_id' => $Qlinks->valueInt('links_id'),
                                   'action' => $action,
                                   'links_url' => $url,
                                   'links_name' => $Qlinks->value('links_name'),
                                   'links_status' => $Qlinks->value('links_status'),
                                   'links_description' => $Qlinks->value('links_description'));
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qlinks->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listStatus()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $records = array();

            $Qstatus = $osC_Database->query("select id,name,state from delta_ticket_status");
            $Qstatus->execute();

            while ($Qstatus->next()) {
                $records[] = array('id' => $Qstatus->valueInt('id'),
                    'name' => $Qstatus->value('name'),
                    'state' => $Qstatus->value('state'));
            }

            $response = array(EXT_JSON_READER_TOTAL => count($records),
                EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function saveDocument()
        {
            global $toC_Json, $osC_Language;

            $data = array('documents_name' => $_REQUEST['documents_name'],
                          'documents_file' => $_FILES['documents_file_name'],
                          'documents_description' => $_REQUEST['documents_description'],
                          'documents_status' => $_REQUEST['documents_status'],
                          'content_id' => $_REQUEST['content_id'],
                          'content_type' => $_REQUEST['content_type']
            );

            if (content::saveDocument((isset($_REQUEST['documents_id']) && ($_REQUEST['documents_id'] != -1)
                        ? $_REQUEST['documents_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function saveComment()
        {
            global $toC_Json, $osC_Language;

            if(isset($_SESSION['admin']) && !empty($_SESSION['admin']))
            {
                $data = array('comment_status' => $_REQUEST['comment_status'],
                    'comments_description' => $_REQUEST['comments_description'],
                    'comment_file_name' => $_FILES['comment_file_name'],
                    'content_id' => $_REQUEST['content_id'],
                    'content_type' => $_REQUEST['content_type']
                );

                if (content::saveComment((isset($_REQUEST['comments_id']) && ($_REQUEST['comments_id'] != -1)
                    ? $_REQUEST['comments_id'] : null), $data)
                ) {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                }
            }
            else
            {
                $response = array('success' => false, 'feedback' => "Votre Session est expiree, Vous devez vous reconnecter !!!");
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function listTickets()
        {
            global $toC_Json, $osC_Database;

            $records = array();

            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

            $customers_id = empty($_REQUEST['customers_id']) ? 0 : $_REQUEST['customers_id'];

            $Qtickets = $osC_Database->query("select t.created,t.tickets_id,t.description,(select user_name from delta_administrators where id = t.administrators_id) as responsable,(select state from delta_ticket_status where id = t.status_id) as status,(select concat(grace_period,'H') from delta_sla where id = t.sla_id) as grace from delta_tickets t where 1 = 1 ");

            if (isset($_REQUEST['start_date'])) {
                //$Qtickets->appendQuery(' and i.event_date >= :start_date');
                //$Qtickets->bindValue(':start_date', $_REQUEST['start_date']);
            }

            if (isset($_REQUEST['end_date'])) {
                //$Qtickets->appendQuery(' and i.event_date <= :end_date');
                //$Qtickets->bindValue(':end_date', "DATE_ADD('" . $_REQUEST['end_date'] . "', INTERVAL 1 DAY)");
            }

            if (isset($_REQUEST['content_id']) && $_REQUEST['content_id'] != '-1') {
                if (isset($_REQUEST['content_type']) && $_REQUEST['content_type'] != 'xx') {
                    switch(strtolower($_REQUEST['content_type']))
                    {
                        case 'customer':
                            $Qtickets->appendQuery(' and t.customers_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;

                        case 'plant':
                            $Qtickets->appendQuery(' and t.plants_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;

                        case 'line':
                            $Qtickets->appendQuery(' and t.lines_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;

                        case 'asset':
                            $Qtickets->appendQuery(' and t.asset_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;

                        case 'component':
                            $Qtickets->appendQuery(' and t.component_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;

                        case 'sensor':
                            $Qtickets->appendQuery(' and t.sensors_id = :content_id');
                            $Qtickets->bindValue(':content_id', $_REQUEST['content_id']);
                            break;
                    }
                }
            }

            $Qtickets->appendQuery(' order by created desc');
            $Qtickets->setExtBatchLimit($start, $limit);
            $Qtickets->execute();

            //var_dump($Qdocuments);

            while ($Qtickets->next()) {
                $file = "xxxxxxx.success";

                switch($Qtickets->value('status'))
                {
                    case "open":
                        $file = "xxxxxxx.open";
                        $action = array(
                            array('class' => 'icon-close-record', 'qtip' => "Cloturer"),
                            array('class' => 'icon-log-record', 'qtip' => "Journal"),
                            array('class' => 'icon-add-record', 'qtip' => "Ajouter un Suivi"));
                        break;
                    case "closed":
                        $file = "xxxxxxx.closed";
                        $action = array(
                            array('class' => 'icon-reopen-record', 'qtip' => "Reouvrir"),
                            array('class' => 'icon-log-record', 'qtip' => "Journal"),
                            array('class' => 'icon-archive-record', 'qtip' => "Archiver"));
                        break;
                    case "archived":
                        $file = "xxxxxxx.zip";
                        $action = array(
                            array('class' => 'icon-reopen-record', 'qtip' => "Reouvrir"),
                            array('class' => 'icon-log-record', 'qtip' => "Journal"),
                            array('class' => 'icon-delete-record', 'qtip' => "Archiver"));
                        break;
                    case "deleted":
                        $file = "xxxxxxx.deleted";
                        break;
                }

                $entry_icon = osc_icon_from_filename($file);

                $records[] = array('tickets_id' => $Qtickets->valueInt('tickets_id'),
                    'icon' => $entry_icon,
                    'created' => $Qtickets->value('created'),
                    'responsable' => $Qtickets->value('responsable'),
                    'status' => $Qtickets->value('status'),
                    'description' => $Qtickets->value('description'),
                    'action' => $action);
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qtickets->getBatchSize(),
                EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function saveTicket()
        {
            global $toC_Json, $osC_Language;

            $data = $_REQUEST;

            if (content::saveTicket((isset($_REQUEST['tickets_id']) && ($_REQUEST['tickets_id'] != -1)
                ? $_REQUEST['tickets_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function closeTicket()
        {
            global $toC_Json,$osC_Database,$osC_Language;

            $data = $_REQUEST;

            $osC_Database->startTransaction();

            $Qticket = $osC_Database->query("update delta_tickets set status_id = 3,isanswered = 1,lastupdate = now(),closed = now() where tickets_id = :tickets_id");
            $Qticket->bindInt(':tickets_id', $data['tickets_id']);

            $Qticket->execute();

            if ($osC_Database->isError()) {
                $response = array('success' => false, 'feedback' => $osC_Database->getError());
                $osC_Database->rollbackTransaction();
            }
            else
            {
                $data['content_type'] = 'ticket';
                $data['comments_status'] = 1;

                if (!content::saveComment(null, $data)
                ) {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
                else
                {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

//                    $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
//                    $body  = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
//                    $body  = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
//                    $body  = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
//                    $body  = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";
//
//                    content::sendNotification($data['content_id'],$event['content_type'],strtolower($config['sensors_current_status'] . '_' . $sensors_status),$event['description'],$body);
                }
            }

            echo $toC_Json->encode($response);
        }

        function suiviTicket()
        {
            global $toC_Json,$osC_Database,$osC_Language;

            $data = $_REQUEST;

            $osC_Database->startTransaction();

            $Qticket = $osC_Database->query("update delta_tickets set isanswered = 1,lastupdate = now() where tickets_id = :tickets_id");
            $Qticket->bindInt(':tickets_id', $data['tickets_id']);

            $Qticket->execute();

            if ($osC_Database->isError()) {

                $response = array('success' => false, 'feedback' => $osC_Database->getError());
                $osC_Database->rollbackTransaction();
            }
            else
            {
                $data['content_id'] = $data['tickets_id'];
                $data['content_type'] = 'ticket';
                $data['comments_status'] = 1;

                if (!content::saveComment(null, $data)
                ) {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
                else
                {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                }
            }

            echo $toC_Json->encode($response);
        }

        function reopenTicket()
        {
            global $toC_Json,$osC_Database,$osC_Language;

            $data = $_REQUEST;

            $osC_Database->startTransaction();

            $Qticket = $osC_Database->query("update delta_tickets set status_id = 1,isanswered = 1,lastupdate = now(),reopened = now() where tickets_id = :tickets_id");
            $Qticket->bindInt(':tickets_id', $data['tickets_id']);

            $Qticket->execute();

            if ($osC_Database->isError()) {
                $response = array('success' => false, 'feedback' => $osC_Database->getError());
                $osC_Database->rollbackTransaction();
            }
            else
            {
                $data['content_id'] = $data['tickets_id'];
                $data['content_type'] = 'ticket';
                $data['comments_status'] = 1;

                if (!content::saveComment(null, $data)
                ) {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
                else
                {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                }
            }

            echo $toC_Json->encode($response);
        }

        function saveLink()
        {
            global $toC_Json, $osC_Language;

            $data = array('links_name' => $_REQUEST['links_name'],
                          'links_description' => $_FILES['links_description'],
                          'links_target' => $_REQUEST['links_target'],
                          'links_status' => $_REQUEST['links_status'],
                          'links_url' => $_REQUEST['links_url'],
                          'content_id' => $_REQUEST['content_id'],
                          'content_type' => $_REQUEST['content_type']
            );

            if (content::saveLink((isset($_REQUEST['links_id']) && ($_REQUEST['links_id'] != -1)
                        ? $_REQUEST['links_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function deleteDocument()
        {
            global $toC_Json, $osC_Language;

            if (content::deleteDocument($_REQUEST['documents_id'], $_REQUEST['documents_name'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function deleteLink()
        {
            global $toC_Json, $osC_Language;

            if (content::deleteLink($_REQUEST['links_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function deleteDocuments()
        {
            global $toC_Json, $osC_Language;

            $error = false;

            $batchs = explode(',', $_REQUEST['batch']);
            foreach ($batchs as $batch) {
                list($documents_id, $filename) = explode(':', $batch);
                if (!content::deleteDocument($documents_id, $filename)) {
                    $error = true;
                    break;
                }
            }

            if ($error === false) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function deleteLinks()
        {
            global $toC_Json, $osC_Language;

            $error = false;

            $batchs = explode(',', $_REQUEST['batch']);
            foreach ($batchs as $batch) {
                if (!content::deleteLink($batch)) {
                    $error = true;
                    break;
                }
            }

            if ($error === false) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function setDocumentStatus()
        {
            global $toC_Json, $osC_Language;

            if (isset($_REQUEST['documents_id']) && content::setDocumentStatus($_REQUEST['documents_id'], (isset($_REQUEST['flag'])
                        ? $_REQUEST['flag'] : null))
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }



        function setCommentStatus()
        {
            global $toC_Json, $osC_Language;

            if (isset($_REQUEST['comments_id']) && content::setCommentStatus($_REQUEST['comments_id'], (isset($_REQUEST['flag'])
                        ? $_REQUEST['flag'] : null))
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function setLinkStatus()
        {
            global $toC_Json, $osC_Language;

            if (isset($_REQUEST['links_id']) && content::setLinkStatus($_REQUEST['links_id'], (isset($_REQUEST['flag'])
                        ? $_REQUEST['flag'] : null))
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function listPermissions()
        {
            global $toC_Json;
            $recs = content::getPermissions($_REQUEST['content_id'], $_REQUEST['content_type']);
            $response = array(EXT_JSON_READER_TOTAL => count($recs),
                              EXT_JSON_READER_ROOT => $recs);

            echo $toC_Json->encode($response);
        }

        function listEvents()
        {
            global $toC_Json;

            $records = array();

            $records[] = array('event' => 'open_ticket', 'label' => 'Ouverture Ticket');
            $records[] = array('event' => 'close_ticket', 'label' => 'Fermeture Ticket');
            $records[] = array('event' => 'reopen_ticket', 'label' => 'ReOuverture Ticket');
            $records[] = array('event' => 'archive_ticket', 'label' => 'Archivage Ticket');

            $records[] = array('event' => 'ok_al', 'label' => 'Changement de Status : OK ==> AL');
            $records[] = array('event' => 'ok_ral', 'label' => 'Changement de Status : OK ==> rAL');
            $records[] = array('event' => 'ok_pal', 'label' => 'Changement de Status : OK ==> pAL');
            $records[] = array('event' => 'ok_error', 'label' => 'Changement de Status : OK ==> ERROR');
            $records[] = array('event' => 'al_ok', 'label' => 'Changement de Status : AL ==> OK');
            $records[] = array('event' => 'al_ral', 'label' => 'Changement de Status : AL ==> rAL');
            $records[] = array('event' => 'al_pal', 'label' => 'Changement de Status : AL ==> pAL');
            $records[] = array('event' => 'al_error', 'label' => 'Changement de Status : AL ==> ERROR');
            $records[] = array('event' => 'ral_ok', 'label' => 'Changement de Status : rAL ==> OK');
            $records[] = array('event' => 'ral_al', 'label' => 'Changement de Status : rAL ==> AL');
            $records[] = array('event' => 'ral_pal', 'label' => 'Changement de Status : rAL ==> pAL');
            $records[] = array('event' => 'ral_error', 'label' => 'Changement de Status : rAL ==> ERROR');
            $records[] = array('event' => 'pal_ok', 'label' => 'Changement de Status : pAL ==> OK');
            $records[] = array('event' => 'pal_ral', 'label' => 'Changement de Status : pAL ==> rAL');
            $records[] = array('event' => 'pal_error', 'label' => 'Changement de Status : pAL ==> ERROR');
            $records[] = array('event' => 'pal_al', 'label' => 'Changement de Status : pAL ==> AL');
            $records[] = array('event' => 'error_ok', 'label' => 'Changement de Status : ERROR ==> OK');
            $records[] = array('event' => 'error_al', 'label' => 'Changement de Status : ERROR ==> AL');
            $records[] = array('event' => 'error_pal', 'label' => 'Changement de Status : ERROR ==> pAL');
            $records[] = array('event' => 'error_ral', 'label' => 'Changement de Status : ERROR ==> rAL');

            $response = array(EXT_JSON_READER_TOTAL => 24,
                EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listNotifications()
        {
            global $toC_Json;
            $recs = content::getNotifications($_REQUEST['content_id'], $_REQUEST['content_type'],$_REQUEST['event'],null);
            $response = array(EXT_JSON_READER_TOTAL => count($recs),
                EXT_JSON_READER_ROOT => $recs);

            echo $toC_Json->encode($response);
        }

        function setPermission()
        {
            global $toC_Json, $osC_Language;
            $data = array('content_id' => $_REQUEST['content_id'], 'content_type' => $_REQUEST['content_type'], 'roles_id' => $_REQUEST['roles_id'], 'permission' => $_REQUEST['permission'], 'flag' => $_REQUEST['flag']);

            if (!isset($_REQUEST['content_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['content_type'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier le type de contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['roles_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['permission'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier une permission pour ce Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (content::setPermission($data['content_id'], $data['content_type'], $data['permission'], $data['roles_id'], $data['flag'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function setNotification()
        {
            global $toC_Json, $osC_Language;
            $data = array('content_id' => $_REQUEST['content_id'], 'content_type' => $_REQUEST['content_type'], 'roles_id' => $_REQUEST['roles_id'],'flag' => $_REQUEST['flag'], 'event' => $_REQUEST['event']);

            if (!isset($_REQUEST['content_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['content_type'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier le type de contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['roles_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['event'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un event');
                echo $toC_Json->encode($response);
                return;
            }

            if (content::setNotification($data['content_id'], $data['content_type'],'notify', $data['roles_id'], $data['flag'],$data['event'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

?>