import { useState } from 'react';
import { shortenUrl } from './services/api';

function App() {
  const [url, setUrl] = useState('');
  const [shortUrl, setShortUrl] = useState(null);
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [copied, setCopied] = useState(false);

  const handleSubmit = async (event) => {
    event.preventDefault();

    const trimmed = url.trim();
    if (!trimmed) {
      setError('Please enter a URL');
      return;
    }

    setError(null);
    setShortUrl(null);
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
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  };

  const handleReset = () => {
    setUrl('');
    setShortUrl(null);
    setError(null);
    setCopied(false);
  };

  return (
    <main className="page">
      <section className="card">
        <h1>URL Shortener</h1>
        <p className="description">
          Convert long URLs into short, shareable links. Paste a destination
          address below to generate a compact redirect.
        </p>

        <form className="shorten-form" onSubmit={handleSubmit}>
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
          <div className="result">
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
                aria-label={copied ? 'Copied to clipboard' : 'Copy to clipboard'}
              >
                {copied ? 'Copied!' : 'Copy'}
              </button>
            </div>
            <button type="button" className="reset-button" onClick={handleReset}>
              Create another
            </button>
          </div>
        )}
      </section>
    </main>
  );
}

export default App;