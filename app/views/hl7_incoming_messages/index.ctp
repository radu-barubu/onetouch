<h1>HL7 Incoming Messages (latest <?php echo $limit; ?>)</h1>
<table>
    <tr>
    	<th>Message Id</th>
    	<th>Date/Timestamp</th>
        <th>Record Type</th>
        <th>Record Id</th>
        <th>Message Text</th>
        <th>Log</th>
    </tr>

    <?php foreach( $hl7_incoming_messages  as $msg ): ?>
    <tr>
        <td><?php echo $msg['Hl7IncomingMessage']['incoming_message_id']; ?></td>
        <td><?php echo str_replace( ' ', '<br>', $msg['Hl7IncomingMessage']['modified_timestamp'] ); ?></td>
        <td><?php echo $msg['Hl7IncomingMessage']['record_type']; ?></td>
        <td><?php echo $msg['Hl7IncomingMessage']['record_id']; ?></td>
        <td><?php foreach( explode( HL7Message::SEGMENT_DELIMITER, $msg['Hl7IncomingMessage']['message_text'] ) as $line ) echo $line . '&crarr;<br>'; ?></ul></td>
        <td><?php echo str_replace( "\n", '<br>', $msg['Hl7IncomingMessage']['log'] ); ?></td>
    </tr>
    <?php endforeach; ?>

</table>