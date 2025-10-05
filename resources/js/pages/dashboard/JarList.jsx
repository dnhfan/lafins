function JarBox({ children }) {
    return (
        <div style={{
            flex: '1 1 0',
            minWidth: 0,
            padding: '12px',
            border: '1px solid #e2e8f0',
            borderRadius: 8,
            background: '#fff',
            boxShadow: '0 1px 2px rgba(0,0,0,0.04)',
            textAlign: 'center'
        }}>
            <p style={{ margin: 0 }}>{children ?? 'box'}</p>
        </div>
    )
}

export default function JarList() {
    return (
        <div style={{
            display: 'flex',
            gap: 16,
            alignItems: 'stretch'
        }}>
            <JarBox>Jar 1</JarBox>
            <JarBox>Jar 2</JarBox>
            <JarBox>Jar 3</JarBox>
            <JarBox>Jar 4</JarBox>
            <JarBox>Jar 5</JarBox>
            <JarBox>Jar 6</JarBox>
            <JarBox>Jar 7</JarBox>
        </div>
    )
}