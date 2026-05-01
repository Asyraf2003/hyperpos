<tr>
    <td>{{ $entry['id'] }}</td>
    <td class="text-nowrap">
        {{ \App\Support\ViewDateFormatter::display($entry['created_at'] ?? null, true) }}
    </td>
    <td>
        <span class="badge bg-light-secondary text-secondary">
            {{ $entry['source'] }}
        </span>
    </td>
    <td>
        <span class="badge bg-light-primary text-primary">
            {{ $entry['event'] }}
        </span>
    </td>
    <td>
        <div>{{ $entry['actor_id'] ?? '-' }}</div>
        @if (!empty($entry['actor_role']))
            <div class="small text-muted">{{ $entry['actor_role'] }}</div>
        @endif
    </td>
    <td>
        <div>{{ $entry['entity_type'] ?? '-' }}</div>
        @if (!empty($entry['entity_id']))
            <div class="small text-muted">{{ $entry['entity_id'] }}</div>
        @endif
        @if (!empty($entry['bounded_context']))
            <div class="small text-muted">{{ $entry['bounded_context'] }}</div>
        @endif
    </td>
    <td>{{ $entry['reason'] }}</td>
    <td>
        <pre class="mb-0 small text-muted" style="white-space: pre-wrap;">{{ $entry['context_json'] }}</pre>
    </td>
</tr>
