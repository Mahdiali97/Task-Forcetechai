function App() {
  return (
    <main className="page">
      <section className="card">
        <h1>URL Shortener</h1>
        <p className="description">
          Convert long URLs into short, shareable links. Paste a destination
          address below to generate a compact redirect.
        </p>

        <form
          className="shorten-form"
          onSubmit={(event) => event.preventDefault()}
        >
          <label htmlFor="url">Long URL</label>
          <input
            id="url"
            name="url"
            type="url"
            placeholder="https://example.com/your/very/long/url"
            autoComplete="url"
          />
          <button type="submit" disabled>
            Shorten
          </button>
        </form>
      </section>
    </main>
  )
}

export default App
