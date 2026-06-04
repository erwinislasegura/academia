<?php

final class WhatsAppLog extends Model
{
    private const FIELDS = [
        'message_id',
        'to_number',
        'template_name',
        'message_type',
        'payload_json',
        'response_json',
        'status_group',
        'status_name',
        'status_description',
        'callback_data',
        'related_module',
        'related_id',
        'error_message',
    ];

    public function create(array $data): int
    {
        $fields = array_values(array_filter(self::FIELDS, static fn(string $field): bool => array_key_exists($field, $data)));
        $columns = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $stmt = $this->db->prepare("INSERT INTO whatsapp_logs ({$columns}) VALUES ({$placeholders})");
        foreach ($fields as $field) {
            $stmt->bindValue(':' . $field, $data[$field]);
        }
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function updateByMessageId(string $messageId, array $data): bool
    {
        $fields = array_values(array_filter(self::FIELDS, static fn(string $field): bool => array_key_exists($field, $data)));
        if ($fields === []) {
            return false;
        }

        $assignments = implode(', ', array_map(static fn(string $field): string => "{$field} = :{$field}", $fields));
        $stmt = $this->db->prepare("UPDATE whatsapp_logs SET {$assignments}, updated_at = CURRENT_TIMESTAMP WHERE message_id = :lookup_message_id");
        foreach ($fields as $field) {
            $stmt->bindValue(':' . $field, $data[$field]);
        }
        $stmt->bindValue(':lookup_message_id', $messageId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function findByMessageId(string $messageId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM whatsapp_logs WHERE message_id = ? LIMIT 1');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function createInboundMessage(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO whatsapp_inbound_messages (from_number, to_number, message_id, message_text, payload_json)
             VALUES (:from_number, :to_number, :message_id, :message_text, :payload_json)'
        );
        $stmt->execute([
            'from_number' => $data['from_number'] ?? null,
            'to_number' => $data['to_number'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'message_text' => $data['message_text'] ?? null,
            'payload_json' => $data['payload_json'] ?? '{}',
        ]);

        return (int) $this->db->lastInsertId();
    }
}
