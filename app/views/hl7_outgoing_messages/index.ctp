<h1>HL7 Outgoing Messages (latest <?php echo $limit; ?>)</h1>
<table>
    <tr>
    	<th>Message Id</th>
    	<th>Date/Timestamp</th>
        <th>Record Type</th>
        <th>Record Id</th>
        <th>Message Text</th>
    </tr>

    <?php foreach( $hl7_outgoing_messages as $msg ): ?>
    <tr>
        <td><?php echo $msg['Hl7OutgoingMessage']['outgoing_message_id']; ?></td>
        <td><?php echo str_replace( ' ', '<br>', $msg['Hl7OutgoingMessage']['modified_timestamp'] ); ?></td>
        <td><?php echo $msg['Hl7OutgoingMessage']['record_type']; ?></td>
        <td><?php echo $msg['Hl7OutgoingMessage']['record_id']; ?></td>
        <td><?php foreach( explode( HL7Message::SEGMENT_DELIMITER, $msg['Hl7OutgoingMessage']['message_text'] ) as $line ) echo $line . '&crarr;<br>'; ?></ul></td>
    </tr>
    <?php endforeach; ?>

</table>