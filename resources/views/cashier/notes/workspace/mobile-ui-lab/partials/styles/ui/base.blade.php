* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #eef2ff;
    color: #172033;
    font-family: Arial, Helvetica, sans-serif;
}

.lab-shell {
    width: min(100%, 720px);
    margin: 0 auto;
    padding: 14px 12px 40px;
}

.lab-top {
    margin-bottom: 12px;
    padding: 18px;
    border-radius: 26px;
    background: #fff;
    box-shadow: 0 18px 50px rgba(15, 23, 42, .10);
}

.lab-top small {
    color: #4f46e5;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.lab-top h1 {
    margin: 7px 0 8px;
    font-size: 1.55rem;
    line-height: 1.1;
}

.lab-top p {
    margin: 0;
    color: #64748b;
    line-height: 1.45;
}

.lab-nav {
    position: sticky;
    top: 0;
    z-index: 20;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 10px 0;
    background: #eef2ff;
}

.lab-nav a {
    min-width: 42px;
    padding: 10px 12px;
    border-radius: 999px;
    background: #fff;
    color: #4f46e5;
    font-weight: 900;
    text-align: center;
    text-decoration: none;
}

.lab-nav a.is-active {
    background: #4f46e5;
    color: #fff;
}
