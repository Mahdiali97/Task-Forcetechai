const API_BASE = '/api';

async function fetchJson(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  });

  const contentType = response.headers.get('content-type') || '';
  const isJson = contentType.includes('application/json');

  let data;
  if (isJson) {
    try {
      data = await response.json();
    } catch {
      throw new Error('Unexpected response from server');
    }
  } else {
    throw new Error(`Unexpected response (${response.status})`);
  }

  if (!response.ok) {
    const message = data?.error || `Request failed with status ${response.status}`;
    throw new Error(message);
  }

  return data;
}

export async function shortenUrl(originalUrl) {
  const data = await fetchJson(`${API_BASE}/shorten.php`, {
    method: 'POST',
    body: JSON.stringify({ url: originalUrl }),
  });

  if (!data?.success || !data?.short_url) {
    throw new Error('Invalid response from server');
  }

  return data.short_url;
}

export async function checkHealth() {
  const data = await fetchJson(`${API_BASE}/health.php`);
  return data.status === 'ok';
}