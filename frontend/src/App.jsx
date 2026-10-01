import { useState } from 'react';
import { shortenUrl } from './services/api';

function App() {
  const [url, setUrl] = useState('');
  const [shortUrl, setShortUrl] = useState(null);
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [copied, setCopied] = useState(false);
  const [copyError, setCopyError] = useState(false);

  const handleSubmit = async (event) => {
    event.preventDefault();

    const trimmed = url.trim();
    if (!trimmed) {
      setError('Please enter a URL');
      return;
    }

    setError(null);
    setShortUrl(null);
    setCopied(false);
    setCopyError(false);
    setIsLoading(true);

    try {
      const result = await shortenUrl(trimmed);
      setShortUrl(result);
    } catch (err) {
      setError(err.message || 'Failed to shorten URL. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleCopy = async () => {
    if (!shortUrl) return;

    try {
      await navigator.clipboard.writeText(shortUrl);
      setCopied(true);
      setCopyError(false);
      setTimeout(() => setCopied(false), 2000);
    } catch {
      setCopied(false);
      setCopyError(true);
    }
  };

  const handleReset = () => {
    setUrl('');
    setShortUrl(null);
    setError(null);
    setCopied(false);
    setCopyError(false);
  };

  return (
    <main className="page">
      <section className="card">
        <h1>URL Shortener</h1>
        <p className="description">
          Convert long URLs into short, shareable links. Paste a destination
          address below to generate a compact redirect.
        </p>

        <form className="shorten-form" onSubmit={handleSubmit} aria-busy={isLoading}>
          <label htmlFor="url">Long URL</label>
          <input
            id="url"
            name="url"
            type="url"
            placeholder="https://example.com/your/very/long/url"
            autoComplete="url"
            value={url}
            onChange={(e) => {
              setUrl(e.target.value);
              if (e.target.value.trim()) setError(null);
            }}
            disabled={isLoading}
            required
            aria-describedby={error ? 'error-message' : undefined}
          />
          {error && (
            <p id="error-message" className="error-message" role="alert">
              {error}
            </p>
          )}
          <button type="submit" disabled={isLoading || !url.trim()}>
            {isLoading ? 'Shortening...' : 'Shorten'}
          </button>
        </form>

        {shortUrl && (
          <section className="result" aria-live="polite" aria-label="Shortened URL result">
            <p className="result-label">Your shortened URL:</p>
            <div className="result-row">
              <a
                href={shortUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="short-url"
              >
                {shortUrl}
              </a>
              <button
                type="button"
                className="copy-button"
                onClick={handleCopy}
                aria-label="Copy shortened URL"
              >
                {copied ? 'Copied!' : 'Copy'}
              </button>
            </div>
            {copied && <p className="copy-message" role="status">Copied to clipboard.</p>}
            {copyError && (
              <p className="copy-message copy-message-error" role="alert">
                Copy failed. Select the URL and copy it manually.
              </p>
            )}
            <button type="button" className="reset-button" onClick={handleReset}>
              Create another
            </button>
          </section>
        )}
      </section>
    </main>
  );
}

export default App;